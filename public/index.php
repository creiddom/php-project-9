<?php

declare(strict_types=1);

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

require dirname(__DIR__) . '/vendor/autoload.php';

session_start();

$flash = new Messages();

/**
 * @return array<int, array{type: string, text: string}>
 */
function flashForTemplate(Messages $flash): array
{
    $result = [];

    foreach ($flash->getMessages() as $type => $messages) {
        foreach ($messages as $message) {
            $result[] = [
                'type' => $type,
                'text' => $message,
            ];
        }
    }

    return $result;
}

function formatCreatedAt(string $createdAt): string
{
    return Carbon::parse($createdAt)->format('Y-m-d');
}

$templatesPath = dirname(__DIR__) . '/templates';
$renderer = new PhpRenderer($templatesPath, [], 'layout.php');

$databaseUrl = parse_url($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: '');

$host = $databaseUrl['host'] ?? 'localhost';
$port = $databaseUrl['port'] ?? 5432;
$dbname = ltrim($databaseUrl['path'] ?? '', '/');

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

if (str_contains($host, 'render.com')) {
    $dsn .= ';sslmode=require';
}

$pdo = new PDO(
    $dsn,
    $databaseUrl['user'] ?? null,
    $databaseUrl['pass'] ?? null
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->get('/', function (Request $request, Response $response) use ($renderer, $flash): Response {
    return $renderer->render($response, 'home.php', [
        'title' => 'Анализатор страниц',
        'flash' => flashForTemplate($flash),
        'errors' => [],
        'urlName' => '',
    ]);
})->setName('home');

$app->get('/urls', function (Request $request, Response $response) use ($renderer, $pdo, $flash): Response {
    $stmt = $pdo->query('SELECT * FROM urls ORDER BY created_at DESC');
    $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($urls as &$url) {
        $url['created_at'] = formatCreatedAt($url['created_at']);
    }
    unset($url);

    return $renderer->render($response, 'urls.php', [
        'title' => 'Сайты',
        'flash' => flashForTemplate($flash),
        'urls' => $urls,
    ]);
})->setName('urls.index');

$app->post('/urls', function (Request $request, Response $response) use ($renderer, $pdo, $flash, $app): Response {
    $data = $request->getParsedBody();
    $urlName = trim((string) ($data['url'] ?? ''));

    $errors = [];

    if ($urlName === '') {
        $errors[] = 'URL не должен быть пустым';
    } elseif (mb_strlen($urlName) > 255) {
        $errors[] = 'URL превышает 255 символов';
    } elseif (!filter_var($urlName, FILTER_VALIDATE_URL)) {
        $errors[] = 'Некорректный URL';
    }

    if ($errors !== []) {
        return $renderer->render($response->withStatus(422), 'home.php', [
            'title' => 'Анализатор страниц',
            'flash' => flashForTemplate($flash),
            'errors' => $errors,
            'urlName' => $urlName,
        ]);
    }

    $parsedUrl = parse_url($urlName);
    $normalizedUrl = "{$parsedUrl['scheme']}://{$parsedUrl['host']}";

    $stmt = $pdo->prepare('SELECT id FROM urls WHERE name = ?');
    $stmt->execute([$normalizedUrl]);
    $existingUrl = $stmt->fetch(PDO::FETCH_ASSOC);

    $routeParser = $app->getRouteCollector()->getRouteParser();

    if ($existingUrl !== false) {
        $flash->addMessage('success', 'Страница уже существует');

        return $response
            ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $existingUrl['id']]))
            ->withStatus(302);
    }

    $createdAt = Carbon::now();

    $stmt = $pdo->prepare('INSERT INTO urls (name, created_at) VALUES (?, ?) RETURNING id');
    $stmt->execute([$normalizedUrl, $createdAt->toDateTimeString()]);
    $id = $stmt->fetchColumn();

    $flash->addMessage('success', 'Страница успешно добавлена');

    return $response
        ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $id]))
        ->withStatus(302);
})->setName('urls.store');

$app->get('/urls/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($renderer, $pdo, $flash): Response {
    $stmt = $pdo->prepare('SELECT * FROM urls WHERE id = ?');
    $stmt->execute([$args['id']]);
    $url = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($url === false) {
        return $response->withStatus(404);
    }

    $url['created_at'] = formatCreatedAt($url['created_at']);

    return $renderer->render($response, 'url.php', [
        'title' => 'Сайт',
        'flash' => flashForTemplate($flash),
        'url' => $url,
    ]);
})->setName('urls.show');

$app->run();

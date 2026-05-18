<?php

declare(strict_types=1);

use App\Http\PageChecker;
use App\SeoExtractor;
use App\Text;
use App\Validator\UrlValidator;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

require dirname(__DIR__) . '/vendor/autoload.php';

session_start();

$flash = new Messages();
$urlValidator = new UrlValidator();
$seoExtractor = new SeoExtractor();
$pageChecker = new PageChecker($seoExtractor);

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
    $stmt = $pdo->query(
        'SELECT urls.*,
            (SELECT status_code FROM url_checks
             WHERE url_id = urls.id
             ORDER BY created_at DESC
             LIMIT 1) AS last_status_code
        FROM urls
        ORDER BY urls.created_at DESC'
    );
    $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($urls as &$url) {
        $url['created_at'] = formatCreatedAt($url['created_at']);
        $url['last_status_code'] = $url['last_status_code'] !== null
            ? (string) $url['last_status_code']
            : '';
    }
    unset($url);

    return $renderer->render($response, 'urls.php', [
        'title' => 'Сайты',
        'flash' => flashForTemplate($flash),
        'urls' => $urls,
    ]);
})->setName('urls.index');

$app->post('/urls', function (Request $request, Response $response) use ($renderer, $pdo, $flash, $app, $urlValidator): Response {
    $data = $request->getParsedBody();
    $urlName = trim((string) ($data['url'] ?? ''));

    $error = $urlValidator->validate($urlName);

    if ($error !== null) {
        $flash->addMessage('danger', $error);

        return $renderer->render($response->withStatus(422), 'home.php', [
            'title' => 'Анализатор страниц',
            'flash' => flashForTemplate($flash),
            'errors' => [$error],
            'urlName' => $urlName,
        ]);
    }

    $normalizedUrl = $urlValidator->normalize($urlName);

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

$app->post('/urls/{id:[0-9]+}/checks', function (Request $request, Response $response, array $args) use ($pdo, $flash, $app, $pageChecker): Response {
    $stmt = $pdo->prepare('SELECT * FROM urls WHERE id = ?');
    $stmt->execute([$args['id']]);
    $url = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($url === false) {
        return $response->withStatus(404);
    }

    $routeParser = $app->getRouteCollector()->getRouteParser();
    $redirect = $response
        ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $args['id']]))
        ->withStatus(302);

    $checkResult = $pageChecker->check($url['name']);

    if (!$checkResult['ok']) {
        $flash->addMessage('danger', $checkResult['error']);

        return $redirect;
    }

    $createdAt = Carbon::now();

    $stmt = $pdo->prepare(
        'INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $args['id'],
        $checkResult['statusCode'],
        $checkResult['seo']['h1'],
        $checkResult['seo']['title'],
        $checkResult['seo']['description'],
        $createdAt->toDateTimeString(),
    ]);

    $flash->addMessage('success', 'Страница успешно проверена');

    return $redirect;
})->setName('urls.checks');

$app->get('/urls/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($renderer, $pdo, $flash): Response {
    $stmt = $pdo->prepare('SELECT * FROM urls WHERE id = ?');
    $stmt->execute([$args['id']]);
    $url = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($url === false) {
        return $response->withStatus(404);
    }

    $url['created_at'] = formatCreatedAt($url['created_at']);

    $stmt = $pdo->prepare('SELECT * FROM url_checks WHERE url_id = ? ORDER BY created_at DESC');
    $stmt->execute([$args['id']]);
    $checks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($checks as &$check) {
        $check['created_at'] = formatCreatedAt($check['created_at']);
        $check['h1'] = Text::forDisplay($check['h1'] !== null ? (string) $check['h1'] : null);
        $check['title'] = Text::forDisplay($check['title'] !== null ? (string) $check['title'] : null);
        $check['description'] = Text::forDisplay($check['description'] !== null ? (string) $check['description'] : null);
    }
    unset($check);

    return $renderer->render($response, 'url.php', [
        'title' => 'Сайт',
        'flash' => flashForTemplate($flash),
        'url' => $url,
        'checks' => $checks,
    ]);
})->setName('urls.show');

$app->run();

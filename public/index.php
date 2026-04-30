<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require dirname(__DIR__) . '/vendor/autoload.php';

$templatesPath = dirname(__DIR__) . '/templates';
$renderer = new PhpRenderer($templatesPath, [], 'layout.php');

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response) use ($renderer): Response {
    return $renderer->render($response, 'home.php', [
        'title' => 'Анализатор страниц',
        'flash' => [],
    ]);
});

$app->run();

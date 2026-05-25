<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\App;

require_once dirname(__DIR__) . '/vendor/autoload.php';

session_start();

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);

$dependencies = require dirname(__DIR__) . '/config/dependencies.php';
$dependencies($containerBuilder);

$container = $containerBuilder->build();

$app = $container->get(App::class);

$routes = require dirname(__DIR__) . '/config/routes.php';
$routes($app, $container);

$app->run();

<?php

declare(strict_types=1);

use App\Database\PdoFactory;
use App\Http\HttpErrorHandler;
use App\View\RoutePresenter;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Views\PhpRenderer;

return static function (ContainerBuilder $builder): void {
    $builder->addDefinitions([
        'settings' => static fn (): array => [
            'displayErrorDetails' => filter_var(
                $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'false',
                FILTER_VALIDATE_BOOLEAN
            ),
            'templatesPath' => dirname(__DIR__) . '/templates',
        ],
        'displayErrorDetails' => static function (ContainerInterface $container): bool {
            $settings = $container->get('settings');

            return $settings['displayErrorDetails'];
        },
        PDO::class => static fn (): PDO => PdoFactory::create(),
        Messages::class => static fn (): Messages => new Messages(),
        PhpRenderer::class => static function (ContainerInterface $container): PhpRenderer {
            $settings = $container->get('settings');

            return new PhpRenderer($settings['templatesPath'], [], 'layout.php');
        },
        ResponseFactoryInterface::class => static fn (): ResponseFactoryInterface => new ResponseFactory(),
        StreamFactoryInterface::class => static fn (): StreamFactoryInterface => new StreamFactory(),
        App::class => static function (ContainerInterface $container): App {
            AppFactory::setContainer($container);

            return AppFactory::create();
        },
        RouteParserInterface::class => static function (ContainerInterface $container): RouteParserInterface {
            return $container->get(App::class)->getRouteCollector()->getRouteParser();
        },
        RoutePresenter::class => static fn (RouteParserInterface $routeParser): RoutePresenter => new RoutePresenter($routeParser),
        HttpErrorHandler::class => static function (ContainerInterface $container): HttpErrorHandler {
            return new HttpErrorHandler(
                $container->get(PhpRenderer::class),
                $container->get(ResponseFactoryInterface::class),
                $container->get('displayErrorDetails'),
            );
        },
    ]);
};

<?php

declare(strict_types=1);

use App\Http\Action\HomeAction;
use App\Http\Action\UrlChecksStoreAction;
use App\Http\Action\UrlsIndexAction;
use App\Http\Action\UrlsShowAction;
use App\Http\Action\UrlsStoreAction;
use App\Http\HttpErrorHandler;
use App\View\RoutePresenter;
use App\View\TemplateFlash;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

return static function (App $app, ContainerInterface $container): void {
    $displayErrorDetails = $container->get('displayErrorDetails');

    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
    $errorMiddleware->setDefaultErrorHandler($container->get(HttpErrorHandler::class));

    $app->addBodyParsingMiddleware();

    $app->get('/', HomeAction::class)->setName('home');
    $app->get('/urls', UrlsIndexAction::class)->setName('urls.index');
    $app->post('/urls', UrlsStoreAction::class)->setName('urls.store');
    $app->post('/urls/{id:[0-9]+}/checks', UrlChecksStoreAction::class)->setName('urls.checks');
    $app->get('/urls/{id:[0-9]+}', UrlsShowAction::class)->setName('urls.show');

    $renderer = $container->get(PhpRenderer::class);
    $renderer->addAttribute('route', $container->get(RoutePresenter::class));
    $renderer->addAttribute('flash', new TemplateFlash($container->get(Messages::class)));
};

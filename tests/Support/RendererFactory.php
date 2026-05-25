<?php

declare(strict_types=1);

namespace Tests\Support;

use App\View\RoutePresenter;
use App\View\TemplateFlash;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

final class RendererFactory
{
    public static function create(): PhpRenderer
    {
        $renderer = new PhpRenderer(dirname(__DIR__, 2) . '/templates', [], 'layout.php');

        $renderer->addAttribute('route', new RoutePresenter(new FakeRouteParser()));
        $renderer->addAttribute('flash', new TemplateFlash(new Messages()));

        return $renderer;
    }
}

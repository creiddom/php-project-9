<?php

declare(strict_types=1);

namespace App\Http\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

final class HomeAction
{
    public function __construct(
        private readonly PhpRenderer $renderer,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, Response $response): Response
    {
        return $this->renderer->render($response, 'home.php', [
            'title' => 'Анализатор страниц',
            'errors' => [],
            'urlName' => '',
        ]);
    }
}

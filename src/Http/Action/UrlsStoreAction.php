<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Http\RedirectResponse;
use App\Service\UrlStoreService;
use App\Validator\UrlFormValidator;
use App\View\RoutePresenter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

final class UrlsStoreAction
{
    public function __construct(
        private readonly PhpRenderer $renderer,
        private readonly Messages $flash,
        private readonly UrlStoreService $urlStoreService,
        private readonly RoutePresenter $route,
        private readonly RedirectResponse $redirectResponse,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $data = is_array($parsedBody) ? $parsedBody : null;
        $result = $this->urlStoreService->store($data);

        if ($result->validation !== null) {
            $validation = $result->validation;
            $error = $validation->errors[0] ?? UrlFormValidator::ERROR_INVALID;
            $this->flash->addMessage('danger', $error);

            return $this->renderer->render($response->withStatus(422), 'home.php', [
                'title' => 'Анализатор страниц',
                'errors' => $validation->errors,
                'urlName' => $validation->url,
            ]);
        }

        $this->flash->addMessage('success', $result->flashMessage);

        return $this->redirectResponse->to(
            $this->route->for('urls.show', ['id' => (string) $result->urlId]),
        );
    }
}

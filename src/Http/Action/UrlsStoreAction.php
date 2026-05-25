<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Http\RedirectResponse;
use App\Repository\UrlRepository;
use App\Support\UrlNormalizer;
use App\Validator\UrlFormValidator;
use App\View\RoutePresenter;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

final class UrlsStoreAction
{
    public function __construct(
        private readonly PhpRenderer $renderer,
        private readonly Messages $flash,
        private readonly UrlFormValidator $urlFormValidator,
        private readonly UrlNormalizer $urlNormalizer,
        private readonly UrlRepository $urlRepository,
        private readonly RoutePresenter $route,
        private readonly RedirectResponse $redirectResponse,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $data = is_array($parsedBody) ? $parsedBody : null;
        $validation = $this->urlFormValidator->validate($data);

        if (!$validation->valid) {
            $error = $validation->errors[0] ?? UrlFormValidator::ERROR_INVALID;
            $this->flash->addMessage('danger', $error);

            return $this->renderer->render($response->withStatus(422), 'home.php', [
                'title' => 'Анализатор страниц',
                'errors' => $validation->errors,
                'urlName' => $validation->url,
            ]);
        }

        $normalizedUrl = $this->urlNormalizer->normalize($validation->url);
        $existingUrl = $this->urlRepository->findIdByName($normalizedUrl);

        if ($existingUrl !== null) {
            $urlId = (string) $existingUrl['id'];
            $flashMessage = 'Страница уже существует';
        } else {
            $createdAt = Carbon::now();
            $urlId = $this->urlRepository->insert($normalizedUrl, $createdAt->toDateTimeString());
            $flashMessage = 'Страница успешно добавлена';
        }

        $this->flash->addMessage('success', $flashMessage);

        return $this->redirectResponse->to($this->route->for('urls.show', ['id' => $urlId]));
    }
}

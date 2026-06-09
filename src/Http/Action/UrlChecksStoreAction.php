<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Http\PageChecker;
use App\Http\RedirectResponse;
use App\Repository\UrlRepository;
use App\View\RoutePresenter;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages;

final class UrlChecksStoreAction
{
    public function __construct(
        private readonly Messages $flash,
        private readonly PageChecker $pageChecker,
        private readonly UrlRepository $urlRepository,
        private readonly RoutePresenter $route,
        private readonly RedirectResponse $redirectResponse,
    ) {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $urlId = (int) $args['id'];
        $url = $this->urlRepository->findById($urlId);

        if ($url === null) {
            throw new HttpNotFoundException($request);
        }

        $redirectUrl = $this->route->for('urls.show', ['id' => (string) $urlId]);

        $checkResult = $this->pageChecker->check((string) $url['name']);

        if (!$checkResult->ok) {
            $this->flash->addMessage('danger', $checkResult->error ?? PageChecker::CONNECTION_ERROR);

            return $this->redirectResponse->to($redirectUrl);
        }

        $createdAt = Carbon::now();
        $seo = $checkResult->seo ?? ['h1' => null, 'title' => null, 'description' => null];

        $this->urlRepository->insertCheck(
            $urlId,
            $checkResult->statusCode,
            $seo['h1'],
            $seo['title'],
            $seo['description'],
            $createdAt->toDateTimeString(),
        );

        $this->flash->addMessage('success', 'Страница успешно проверена');

        return $this->redirectResponse->to($redirectUrl);
    }
}

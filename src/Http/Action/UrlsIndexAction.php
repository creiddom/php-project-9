<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Repository\UrlRepository;
use App\View\UrlPresenter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

final class UrlsIndexAction
{
    public function __construct(
        private readonly PhpRenderer $renderer,
        private readonly UrlRepository $urlRepository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, Response $response): Response
    {
        $urls = $this->urlRepository->findAllOrdered();
        $statusByUrlId = $this->urlRepository->findLatestStatusCodeByUrlId();

        return $this->renderer->render($response, 'urls/index.php', [
            'title' => 'Сайты',
            'urls' => UrlPresenter::forIndexList($urls, $statusByUrlId),
        ]);
    }
}

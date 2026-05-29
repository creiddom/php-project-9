<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Repository\UrlRepository;
use App\View\UrlPresenter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

final class UrlsIndexAction
{
    public function __construct(
        private readonly PhpRenderer $renderer,
        private readonly UrlRepository $urlRepository,
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        unset($request);

        $urls = $this->urlRepository->findAllOrdered();

        return $this->renderer->render($response, 'urls.php', [
            'title' => 'Сайты',
            'urls' => UrlPresenter::forIndexList($urls),
        ]);
    }
}

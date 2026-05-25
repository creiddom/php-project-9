<?php

declare(strict_types=1);

namespace App\Http\Action;

use App\Repository\UrlRepository;
use App\View\UrlPresenter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\PhpRenderer;

final class UrlsShowAction
{
    public function __construct(
        private readonly PhpRenderer $renderer,
        private readonly UrlRepository $urlRepository,
    ) {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $url = $this->urlRepository->findById($args['id']);

        if ($url === null) {
            throw new HttpNotFoundException($request);
        }

        $checks = $this->urlRepository->findChecksByUrlId($args['id']);

        return $this->renderer->render($response, 'url.php', [
            'title' => 'Сайт',
            'url' => UrlPresenter::forShowPage($url),
            'checks' => UrlPresenter::forChecksList($checks),
        ]);
    }
}

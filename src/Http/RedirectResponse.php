<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Slim\Http\Response as DecoratedResponse;

final class RedirectResponse
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    public function to(string $url, int $status = 302): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();

        return (new DecoratedResponse($response, $this->streamFactory))->withRedirect($url, $status);
    }
}

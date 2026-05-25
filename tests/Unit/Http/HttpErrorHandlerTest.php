<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\HttpErrorHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\RendererFactory;

final class HttpErrorHandlerTest extends TestCase
{
    public function testNotFoundExceptionRenders404(): void
    {
        $handler = $this->createHandler();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/missing');

        $response = $handler(
            $request,
            new HttpNotFoundException($request),
            false,
            true,
            true,
        );

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testMethodNotAllowedRenders404(): void
    {
        $handler = $this->createHandler();
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/');

        $exception = new HttpMethodNotAllowedException($request);
        $exception->setAllowedMethods(['GET']);

        $response = $handler($request, $exception, false, true, true);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testHttpExceptionRendersStatusCode(): void
    {
        $handler = $this->createHandler();
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');

        $response = $handler(
            $request,
            new HttpBadRequestException($request, 'bad input'),
            false,
            true,
            true,
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testGenericExceptionRenders500(): void
    {
        $handler = $this->createHandler();

        $response = $handler(
            (new ServerRequestFactory())->createServerRequest('GET', '/'),
            new RuntimeException('db down'),
            false,
            true,
            true,
        );

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testDisplayErrorDetailsShowsMessage(): void
    {
        $handler = $this->createHandler(displayErrorDetails: true);

        $response = $handler(
            (new ServerRequestFactory())->createServerRequest('GET', '/'),
            new RuntimeException('secret'),
            true,
            true,
            true,
        );

        $this->assertSame(500, $response->getStatusCode());
    }

    private function createHandler(bool $displayErrorDetails = false): HttpErrorHandler
    {
        return new HttpErrorHandler(
            RendererFactory::create(),
            new ResponseFactory(),
            $displayErrorDetails,
        );
    }
}

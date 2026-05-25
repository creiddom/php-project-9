<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Action;

use App\Http\Action\UrlsStoreAction;
use App\Http\RedirectResponse;
use App\Repository\UrlRepository;
use App\Support\UrlNormalizer;
use App\Validator\UrlFormValidator;
use App\View\RoutePresenter;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Tests\Support\RendererFactory;

final class UrlsStoreActionTest extends TestCase
{
    public function testInvokeReturns422OnInvalidUrl(): void
    {
        $action = $this->createAction();
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/urls')
            ->withParsedBody(['url' => '']);

        $response = $action($request, (new ResponseFactory())->createResponse());

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testInvokeRedirectsWhenUrlCreated(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findIdByName')->willReturn(null);
        $repository->method('insert')->willReturn('10');

        $action = $this->createAction($repository);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/urls')
            ->withParsedBody(['url' => 'https://example.com/page']);

        $response = $action($request, (new ResponseFactory())->createResponse());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/urls/10', $response->getHeaderLine('Location'));
    }

    public function testInvokeRedirectsWhenUrlExists(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findIdByName')->willReturn(['id' => 3]);

        $action = $this->createAction($repository);
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/urls')
            ->withParsedBody(['url' => 'https://ya.ru']);

        $response = $action($request, (new ResponseFactory())->createResponse());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/urls/3', $response->getHeaderLine('Location'));
    }

    private function createAction(?UrlRepository $repository = null): UrlsStoreAction
    {
        $repository ??= $this->createMock(UrlRepository::class);
        $parser = $this->createMock(RouteParserInterface::class);
        $parser->method('urlFor')
            ->willReturnCallback(
                static fn (string $name, array $data): string => '/urls/' . $data['id'],
            );

        return new UrlsStoreAction(
            RendererFactory::create(),
            new Messages(),
            new UrlFormValidator(),
            new UrlNormalizer(),
            $repository,
            new RoutePresenter($parser),
            new RedirectResponse(new ResponseFactory(), new StreamFactory()),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Action;

use App\Http\Action\UrlChecksStoreAction;
use App\Http\CheckResult;
use App\Http\PageChecker;
use App\Http\RedirectResponse;
use App\Repository\UrlRepository;
use App\View\RoutePresenter;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

final class UrlChecksStoreActionTest extends TestCase
{
    public function testInvokeThrowsWhenUrlMissing(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findById')->willReturn(null);

        $action = $this->createAction($repository);

        $this->expectException(HttpNotFoundException::class);

        $action(
            (new ServerRequestFactory())->createServerRequest('POST', '/urls/9/checks'),
            (new ResponseFactory())->createResponse(),
            ['id' => '9'],
        );
    }

    public function testInvokeRedirectsOnConnectionError(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findById')->willReturn(['id' => 1, 'name' => 'https://bad.test']);

        $checker = $this->createMock(PageChecker::class);
        $checker->method('check')->willReturn(CheckResult::failed(PageChecker::CONNECTION_ERROR));

        $action = $this->createAction($repository, $checker);
        $response = $action(
            (new ServerRequestFactory())->createServerRequest('POST', '/urls/1/checks'),
            (new ResponseFactory())->createResponse(),
            ['id' => '1'],
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/urls/1', $response->getHeaderLine('Location'));
    }

    public function testInvokeInsertsCheckOnSuccess(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findById')->willReturn(['id' => 2, 'name' => 'https://ok.test']);
        $repository->expects($this->once())->method('insertCheck');

        $checker = $this->createMock(PageChecker::class);
        $checker->method('check')->willReturn(CheckResult::succeeded(200, [
            'h1' => 'h',
            'title' => 't',
            'description' => 'd',
        ]));

        $action = $this->createAction($repository, $checker);
        $response = $action(
            (new ServerRequestFactory())->createServerRequest('POST', '/urls/2/checks'),
            (new ResponseFactory())->createResponse(),
            ['id' => '2'],
        );

        $this->assertSame(302, $response->getStatusCode());
    }

    private function createAction(
        UrlRepository $repository,
        ?PageChecker $checker = null,
    ): UrlChecksStoreAction {
        $checker ??= $this->createMock(PageChecker::class);
        $parser = $this->createMock(RouteParserInterface::class);
        $parser->method('urlFor')->willReturnCallback(
            static fn (string $name, array $data): string => '/urls/' . $data['id'],
        );

        $storage = [];

        return new UrlChecksStoreAction(
            new Messages($storage),
            $checker,
            $repository,
            new RoutePresenter($parser),
            new RedirectResponse(new ResponseFactory(), new StreamFactory()),
        );
    }
}

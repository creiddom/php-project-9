<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Action;

use App\Http\Action\UrlsShowAction;
use App\Repository\UrlRepository;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\RendererFactory;

final class UrlsShowActionTest extends TestCase
{
    public function testInvokeRendersUrlPage(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findById')->willReturn([
            'id' => 1,
            'name' => 'https://a.ru',
            'created_at' => '2024-01-01 00:00:00',
        ]);
        $repository->method('findChecksByUrlId')->willReturn([]);

        $action = new UrlsShowAction(RendererFactory::create(), $repository);

        $result = $action(
            (new ServerRequestFactory())->createServerRequest('GET', '/urls/1'),
            (new ResponseFactory())->createResponse(),
            ['id' => '1'],
        );

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testInvokeThrowsWhenUrlMissing(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findById')->willReturn(null);

        $action = new UrlsShowAction(RendererFactory::create(), $repository);

        $this->expectException(HttpNotFoundException::class);

        $action(
            (new ServerRequestFactory())->createServerRequest('GET', '/urls/99'),
            (new ResponseFactory())->createResponse(),
            ['id' => '99'],
        );
    }
}

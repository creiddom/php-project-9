<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Action;

use App\Http\Action\UrlsIndexAction;
use App\Repository\UrlRepository;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\RendererFactory;

final class UrlsIndexActionTest extends TestCase
{
    public function testInvokeRendersUrlsList(): void
    {
        $repository = $this->createMock(UrlRepository::class);
        $repository->method('findAllOrdered')->willReturn([
            ['id' => 1, 'name' => 'https://a.ru', 'created_at' => '2024-01-01 00:00:00'],
        ]);
        $repository->method('findLatestStatusCodeByUrlId')->willReturn([1 => 200]);

        $renderer = RendererFactory::create();
        $action = new UrlsIndexAction($renderer, $repository);

        $result = $action(
            (new ServerRequestFactory())->createServerRequest('GET', '/urls'),
            (new ResponseFactory())->createResponse(),
        );

        $this->assertSame(200, $result->getStatusCode());
        $this->assertStringContainsString('Сайты', (string) $result->getBody());
    }
}

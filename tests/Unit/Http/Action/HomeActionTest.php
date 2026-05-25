<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Action;

use App\Http\Action\HomeAction;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\RendererFactory;

final class HomeActionTest extends TestCase
{
    public function testInvokeRendersHomePage(): void
    {
        $renderer = RendererFactory::create();
        $action = new HomeAction($renderer);
        $response = (new ResponseFactory())->createResponse();

        $result = $action((new ServerRequestFactory())->createServerRequest('GET', '/'), $response);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertStringContainsString('Анализатор страниц', (string) $result->getBody());
    }
}

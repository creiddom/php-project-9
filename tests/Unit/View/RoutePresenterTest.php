<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use App\View\RoutePresenter;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteParserInterface;

final class RoutePresenterTest extends TestCase
{
    public function testForDelegatesToRouteParser(): void
    {
        $parser = $this->createMock(RouteParserInterface::class);
        $parser->method('urlFor')
            ->with('urls.show', ['id' => '7'])
            ->willReturn('/urls/7');

        $presenter = new RoutePresenter($parser);

        $this->assertSame('/urls/7', $presenter->for('urls.show', ['id' => 7]));
    }
}

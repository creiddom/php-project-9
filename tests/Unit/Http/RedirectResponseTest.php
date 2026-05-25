<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\RedirectResponse;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

final class RedirectResponseTest extends TestCase
{
    public function testToSetsLocationAndStatus(): void
    {
        $redirect = new RedirectResponse(new ResponseFactory(), new StreamFactory());
        $response = $redirect->to('/urls/1', 302);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/urls/1', $response->getHeaderLine('Location'));
    }
}

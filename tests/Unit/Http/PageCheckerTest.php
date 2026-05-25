<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\CheckResult;
use App\Http\PageChecker;
use App\SeoExtractor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class PageCheckerTest extends TestCase
{
    public function testCheckSucceedsWithHtml(): void
    {
        $html = '<html><head><title>T</title><meta name="description" content="D"></head>'
            . '<body><h1>H</h1></body></html>';
        $client = $this->clientWithResponses([new Response(200, [], $html)]);

        $result = (new PageChecker(new SeoExtractor(), $client))->check('https://example.com');

        $this->assertTrue($result->ok);
        $this->assertSame(200, $result->statusCode);
        $this->assertSame('H', $result->seo['h1']);
    }

    public function testCheckFailsOnNonSuccessStatus(): void
    {
        $client = $this->clientWithResponses([new Response(404, [], '')]);

        $result = (new PageChecker(new SeoExtractor(), $client))->check('https://example.com');

        $this->assertFalse($result->ok);
        $this->assertSame(PageChecker::CONNECTION_ERROR, $result->error);
    }

    public function testCheckFailsOnConnectionError(): void
    {
        $mock = new MockHandler([
            new ConnectException('fail', new Request('GET', 'https://example.com')),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $result = (new PageChecker(new SeoExtractor(), $client))->check('https://example.com');

        $this->assertInstanceOf(CheckResult::class, $result);
        $this->assertFalse($result->ok);
    }

    public function testCheckUsesResponseFromRequestException(): void
    {
        $html = '<html><head><title>T</title></head><body><h1>H</h1></body></html>';
        $request = new Request('GET', 'https://example.com');
        $mock = new MockHandler([
            new RequestException('err', $request, new Response(200, [], $html)),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $result = (new PageChecker(new SeoExtractor(), $client))->check('https://example.com');

        $this->assertTrue($result->ok);
    }

    public function testCheckSucceedsWhenSeoExtractorThrows(): void
    {
        $seo = $this->createMock(SeoExtractor::class);
        $seo->method('extract')->willThrowException(new \RuntimeException('parse'));
        $client = $this->clientWithResponses([new Response(200, [], '<html></html>')]);

        $result = (new PageChecker($seo, $client))->check('https://example.com');

        $this->assertTrue($result->ok);
        $this->assertNull($result->seo['h1']);
    }

    /**
     * @param list<Response> $responses
     */
    private function clientWithResponses(array $responses): Client
    {
        return new Client(['handler' => HandlerStack::create(new MockHandler($responses))]);
    }
}

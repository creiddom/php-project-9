<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\UrlNormalizer;
use PHPUnit\Framework\TestCase;

final class UrlNormalizerTest extends TestCase
{
    public function testNormalizeKeepsSchemeHostAndPort(): void
    {
        $normalizer = new UrlNormalizer();

        $this->assertSame(
            'https://example.com:8080',
            $normalizer->normalize('https://Example.com:8080/path?q=1'),
        );
    }

    public function testNormalizeWithoutPort(): void
    {
        $normalizer = new UrlNormalizer();

        $this->assertSame(
            'https://ya.ru',
            $normalizer->normalize('http://ya.ru/page'),
        );
    }

    public function testNormalizeUsesHttpsForHttpScheme(): void
    {
        $normalizer = new UrlNormalizer();

        $this->assertSame(
            'https://example.com',
            $normalizer->normalize('http://example.com'),
        );
        $this->assertSame(
            $normalizer->normalize('https://example.com'),
            $normalizer->normalize('http://example.com'),
        );
    }

    public function testNormalizeStripsWwwPrefix(): void
    {
        $normalizer = new UrlNormalizer();

        $this->assertSame(
            'https://example.com',
            $normalizer->normalize('https://www.example.com/path'),
        );
        $this->assertSame(
            $normalizer->normalize('https://example.com'),
            $normalizer->normalize('https://www.example.com'),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Validator;

use App\Validator\UrlFormValidator;
use PHPUnit\Framework\TestCase;

final class UrlFormValidatorTest extends TestCase
{
    private UrlFormValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UrlFormValidator();
    }

    public function testValidUrl(): void
    {
        $result = $this->validator->validate(['url' => 'https://example.com/path']);

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->errors);
        $this->assertSame('https://example.com/path', $result->url);
    }

    public function testValidUrlWithTrailingSpace(): void
    {
        $result = $this->validator->validate(['url' => 'https://mail.ru ']);

        $this->assertTrue($result->valid);
        $this->assertSame('https://mail.ru', $result->url);
    }

    public function testEmptyUrl(): void
    {
        $result = $this->validator->validate(['url' => '']);

        $this->assertFalse($result->valid);
        $this->assertContains(UrlFormValidator::ERROR_EMPTY, $result->errors);
    }

    public function testWhitespaceOnlyUrl(): void
    {
        $result = $this->validator->validate(['url' => '   ']);

        $this->assertFalse($result->valid);
        $this->assertContains(UrlFormValidator::ERROR_EMPTY, $result->errors);
    }

    public function testMissingUrlField(): void
    {
        $result = $this->validator->validate([]);

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->errors);
    }

    public function testTooLongUrl(): void
    {
        $result = $this->validator->validate(['url' => 'https://example.com/' . str_repeat('a', 250)]);

        $this->assertFalse($result->valid);
        $this->assertContains(UrlFormValidator::ERROR_TOO_LONG, $result->errors);
    }

    public function testInvalidUrl(): void
    {
        $result = $this->validator->validate(['url' => 'not-a-url']);

        $this->assertFalse($result->valid);
        $this->assertContains(UrlFormValidator::ERROR_INVALID, $result->errors);
    }

    public function testUrlWithSpaces(): void
    {
        $result = $this->validator->validate(['url' => 'https://exa mple.com']);

        $this->assertFalse($result->valid);
    }

    public function testUrlWithCredentials(): void
    {
        $result = $this->validator->validate(['url' => 'https://user:pass@example.com']);

        $this->assertFalse($result->valid);
    }

    public function testNullData(): void
    {
        $result = $this->validator->validate(null);

        $this->assertFalse($result->valid);
    }
}

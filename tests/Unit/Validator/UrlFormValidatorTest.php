<?php

declare(strict_types=1);

namespace Tests\Unit\Validator;

use App\Validator\UrlFormValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlFormValidatorTest extends TestCase
{
    private UrlFormValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UrlFormValidator();
    }

    #[DataProvider('validUrlProvider')]
    public function testValidUrl(string $input, string $expected): void
    {
        $result = $this->validator->validate(['url' => $input]);

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->errors);
        $this->assertSame($expected, $result->url);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validUrlProvider(): iterable
    {
        yield 'plain url' => ['https://example.com/path', 'https://example.com/path'];
        yield 'trailing space' => ['https://mail.ru ', 'https://mail.ru'];
    }

    #[DataProvider('invalidUrlProvider')]
    public function testInvalidUrl(?array $data, string $expectedError): void
    {
        $result = $this->validator->validate($data);

        $this->assertFalse($result->valid);
        $this->assertContains($expectedError, $result->errors);
        $this->assertCount(1, $result->errors);
    }

    /**
     * @return iterable<string, array{?array<string, mixed>, string}>
     */
    public static function invalidUrlProvider(): iterable
    {
        yield 'empty' => [['url' => ''], UrlFormValidator::ERROR_EMPTY];
        yield 'whitespace only' => [['url' => '   '], UrlFormValidator::ERROR_EMPTY];
        yield 'missing field' => [[], UrlFormValidator::ERROR_EMPTY];
        yield 'null data' => [null, UrlFormValidator::ERROR_EMPTY];
        yield 'too long' => [
            ['url' => 'https://example.com/' . str_repeat('a', 250)],
            UrlFormValidator::ERROR_TOO_LONG,
        ];
        yield 'not a url' => [['url' => 'not-a-url'], UrlFormValidator::ERROR_INVALID];
        yield 'spaces in host' => [['url' => 'https://exa mple.com'], UrlFormValidator::ERROR_INVALID];
    }
}

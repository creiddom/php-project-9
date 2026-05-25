<?php

declare(strict_types=1);

namespace Tests\Unit\Validator;

use App\Validator\UrlValidationResult;
use PHPUnit\Framework\TestCase;

final class UrlValidationResultTest extends TestCase
{
    public function testProperties(): void
    {
        $result = new UrlValidationResult(true, [], 'https://a.ru');

        $this->assertTrue($result->valid);
        $this->assertSame([], $result->errors);
        $this->assertSame('https://a.ru', $result->url);
    }
}

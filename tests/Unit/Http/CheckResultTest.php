<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\CheckResult;
use PHPUnit\Framework\TestCase;

final class CheckResultTest extends TestCase
{
    public function testFailedResult(): void
    {
        $result = CheckResult::failed('ошибка');

        $this->assertFalse($result->ok);
        $this->assertSame('ошибка', $result->error);
        $this->assertNull($result->statusCode);
    }

    public function testSucceededResult(): void
    {
        $seo = ['h1' => 'h', 'title' => 't', 'description' => 'd'];
        $result = CheckResult::succeeded(200, $seo);

        $this->assertTrue($result->ok);
        $this->assertSame(200, $result->statusCode);
        $this->assertSame($seo, $result->seo);
    }
}

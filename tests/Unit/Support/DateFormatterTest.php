<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\DateFormatter;
use PHPUnit\Framework\TestCase;

final class DateFormatterTest extends TestCase
{
    public function testFormatCreatedAt(): void
    {
        $this->assertSame('2024-05-25', DateFormatter::formatCreatedAt('2024-05-25 12:00:00'));
    }
}

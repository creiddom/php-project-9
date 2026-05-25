<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Text;
use PHPUnit\Framework\TestCase;

final class TextTest extends TestCase
{
    public function testLimitForStorageReturnsNullForEmpty(): void
    {
        $this->assertNull(Text::limitForStorage(null));
        $this->assertNull(Text::limitForStorage('   '));
    }

    public function testLimitForStorageTrimsAndTruncates(): void
    {
        $this->assertSame('hello', Text::limitForStorage('  hello  '));
        $this->assertSame(str_repeat('a', 255), Text::limitForStorage(str_repeat('a', 300)));
    }

    public function testForDisplayReturnsEmptyForNull(): void
    {
        $this->assertSame('', Text::forDisplay(null));
        $this->assertSame('', Text::forDisplay(''));
    }

    public function testForDisplayTruncatesLongText(): void
    {
        $short = 'короткий текст';
        $this->assertSame($short, Text::forDisplay($short));

        $long = str_repeat('б', 250);
        $this->assertSame(mb_substr($long, 0, 200) . '...', Text::forDisplay($long, 200));
    }
}

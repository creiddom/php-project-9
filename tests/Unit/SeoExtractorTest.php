<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\SeoExtractor;
use PHPUnit\Framework\TestCase;

final class SeoExtractorTest extends TestCase
{
    public function testExtractFromValidHtml(): void
    {
        $html = <<<'HTML'
        <!DOCTYPE html>
        <html><head>
        <title>Page Title</title>
        <meta name="description" content="Page description">
        </head><body><h1>Heading</h1></body></html>
        HTML;

        $seo = (new SeoExtractor())->extract($html);

        $this->assertSame('Heading', $seo['h1']);
        $this->assertSame('Page Title', $seo['title']);
        $this->assertSame('Page description', $seo['description']);
    }

    public function testExtractFromEmptyHtml(): void
    {
        $seo = (new SeoExtractor())->extract('   ');

        $this->assertNull($seo['h1']);
        $this->assertNull($seo['title']);
        $this->assertNull($seo['description']);
    }

    public function testExtractFromBrokenHtml(): void
    {
        $seo = (new SeoExtractor())->extract('<html><unclosed>');

        $this->assertNull($seo['h1']);
    }

    public function testExtractWhenTagsAreMissing(): void
    {
        $seo = (new SeoExtractor())->extract('<html><head></head><body></body></html>');

        $this->assertNull($seo['h1']);
        $this->assertNull($seo['title']);
        $this->assertNull($seo['description']);
    }
}

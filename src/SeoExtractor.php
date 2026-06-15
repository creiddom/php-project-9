<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\DomCrawler\Crawler;

final class SeoExtractor
{
    private const VARCHAR_FIELD_MAX = 1024;

    /**
     * @return array{h1: ?string, title: ?string, description: ?string}
     */
    public function extract(string $html): array
    {
        if (trim($html) === '') {
            return $this->emptySeo();
        }

        $crawler = new Crawler();
        $crawler->addHtmlContent($html, 'UTF-8');

        return [
            'h1' => $this->nodeText($crawler, 'h1'),
            'title' => $this->nodeText($crawler, 'title'),
            'description' => $this->metaDescription($crawler),
        ];
    }

    /**
     * @return array{h1: null, title: null, description: null}
     */
    private function emptySeo(): array
    {
        return [
            'h1' => null,
            'title' => null,
            'description' => null,
        ];
    }

    private function nodeText(Crawler $crawler, string $selector): ?string
    {
        $nodes = $crawler->filter($selector);
        if ($nodes->count() === 0) {
            return null;
        }

        return Text::limitForStorage($nodes->first()->text(''), self::VARCHAR_FIELD_MAX);
    }

    private function metaDescription(Crawler $crawler): ?string
    {
        $nodes = $crawler->filter('meta[name="description"]');
        if ($nodes->count() === 0) {
            return null;
        }

        $content = $nodes->first()->attr('content');
        if ($content === null || trim($content) === '') {
            return null;
        }

        return trim($content);
    }
}

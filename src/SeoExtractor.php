<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\DomCrawler\Crawler;

final class SeoExtractor
{
    /**
     * @return array{h1: ?string, title: ?string, description: ?string}
     */
    public function extract(string $html): array
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);

        return [
            'h1' => $this->nodeText($crawler, 'h1'),
            'title' => $this->nodeText($crawler, 'title'),
            'description' => $this->metaDescription($crawler),
        ];
    }

    private function nodeText(Crawler $crawler, string $selector): ?string
    {
        $nodes = $crawler->filter($selector);

        if ($nodes->count() === 0) {
            return null;
        }

        return Text::limitForStorage($nodes->first()->text(''));
    }

    private function metaDescription(Crawler $crawler): ?string
    {
        $nodes = $crawler->filter('meta[name="description"]');

        if ($nodes->count() === 0) {
            return null;
        }

        $content = $nodes->first()->attr('content');

        return Text::limitForStorage($content !== null ? (string) $content : null);
    }
}

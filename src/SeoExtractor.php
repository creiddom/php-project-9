<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\DomCrawler\Crawler;
use Throwable;

final class SeoExtractor
{
    /**
     * @return array{h1: ?string, title: ?string, description: ?string}
     */
    public function extract(string $html): array
    {
        $empty = [
            'h1' => null,
            'title' => null,
            'description' => null,
        ];

        if (trim($html) === '') {
            return $empty;
        }

        try {
            $crawler = new Crawler();
            $crawler->addHtmlContent($html, 'UTF-8');
        } catch (Throwable) {
            return $empty;
        }

        return [
            'h1' => $this->nodeText($crawler, 'h1'),
            'title' => $this->nodeText($crawler, 'title'),
            'description' => $this->metaDescription($crawler),
        ];
    }

    private function nodeText(Crawler $crawler, string $selector): ?string
    {
        try {
            $nodes = $crawler->filter($selector);
        } catch (Throwable) {
            return null;
        }

        if ($nodes->count() === 0) {
            return null;
        }

        return Text::limitForStorage($nodes->first()->text(''));
    }

    private function metaDescription(Crawler $crawler): ?string
    {
        try {
            $nodes = $crawler->filter('meta[name="description"]');
        } catch (Throwable) {
            return null;
        }

        if ($nodes->count() === 0) {
            return null;
        }

        $content = $nodes->first()->attr('content');

        return Text::limitForStorage($content !== null ? (string) $content : null);
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

final class UrlNormalizer
{
    public function normalize(string $url): string
    {
        $parsed = parse_url(trim($url));
        $scheme = strtolower($parsed['scheme'] ?? '');
        if ($scheme === 'http') {
            $scheme = 'https';
        }
        $host = strtolower($parsed['host'] ?? '');
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }
        $normalized = "{$scheme}://{$host}";

        if (isset($parsed['port'])) {
            $normalized .= ':' . (int) $parsed['port'];
        }

        return $normalized;
    }
}

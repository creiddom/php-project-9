<?php

declare(strict_types=1);

namespace App\Support;

final class UrlNormalizer
{
    public function normalize(string $url): string
    {
        $parsed = parse_url(trim($url));
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');
        $normalized = "{$scheme}://{$host}";

        if (isset($parsed['port'])) {
            $normalized .= ':' . (int) $parsed['port'];
        }

        return $normalized;
    }
}

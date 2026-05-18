<?php

declare(strict_types=1);

namespace App\Validator;

final class UrlValidator
{
    public const ERROR_EMPTY = 'URL не должен быть пустым';

    public const ERROR_TOO_LONG = 'URL превышает 255 символов';

    public const ERROR_INVALID = 'Некорректный URL';

    public function validate(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return self::ERROR_EMPTY;
        }

        if (mb_strlen($url) > 255) {
            return self::ERROR_TOO_LONG;
        }

        if (!$this->isValidUrl($url)) {
            return self::ERROR_INVALID;
        }

        return null;
    }

    public function normalize(string $url): string
    {
        $parsed = parse_url(trim($url));

        return "{$parsed['scheme']}://{$parsed['host']}";
    }

    private function isValidUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? '';
        $host = $parsed['host'] ?? '';

        return in_array($scheme, ['http', 'https'], true) && $host !== '';
    }
}

<?php

declare(strict_types=1);

namespace App;

final class Text
{
    public static function limitForStorage(?string $value, int $maxLength = 255): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (mb_strlen($value) > $maxLength) {
            return mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    public static function forDisplay(?string $value, int $maxLength = 200): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength) . '...';
    }
}

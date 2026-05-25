<?php

declare(strict_types=1);

namespace App\View;

use App\Support\DateFormatter;
use App\Text;

final class UrlPresenter
{
    /**
     * @param list<array<string, mixed>>     $urls
     * @param array<int|string, mixed>       $statusByUrlId
     *
     * @return list<array<string, mixed>>
     */
    public static function forIndexList(array $urls, array $statusByUrlId): array
    {
        return array_map(
            static function (array $url) use ($statusByUrlId): array {
                $urlId = $url['id'];
                $statusCode = $statusByUrlId[$urlId] ?? null;

                return [
                    'id' => $url['id'],
                    'name' => $url['name'],
                    'created_at' => DateFormatter::formatCreatedAt((string) $url['created_at']),
                    'last_status_code' => $statusCode !== null ? (string) $statusCode : '',
                ];
            },
            $urls,
        );
    }

    /**
     * @param array<string, mixed> $url
     *
     * @return array<string, mixed>
     */
    public static function forShowPage(array $url): array
    {
        return [
            'id' => $url['id'],
            'name' => $url['name'],
            'created_at' => DateFormatter::formatCreatedAt((string) $url['created_at']),
        ];
    }

    /**
     * @param list<array<string, mixed>> $checks
     *
     * @return list<array<string, mixed>>
     */
    public static function forChecksList(array $checks): array
    {
        return array_map(
            static fn (array $check): array => [
                'id' => $check['id'],
                'status_code' => $check['status_code'],
                'h1' => Text::forDisplay($check['h1'] !== null ? (string) $check['h1'] : null),
                'title' => Text::forDisplay($check['title'] !== null ? (string) $check['title'] : null),
                'description' => Text::forDisplay(
                    $check['description'] !== null ? (string) $check['description'] : null
                ),
                'created_at' => DateFormatter::formatCreatedAt((string) $check['created_at']),
            ],
            $checks,
        );
    }
}

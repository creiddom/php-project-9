<?php

declare(strict_types=1);

namespace App\Http;

final class CheckResult
{
    /**
     * @param array{h1: ?string, title: ?string, description: ?string}|null $seo
     */
    private function __construct(
        public readonly bool $ok,
        public readonly ?string $error = null,
        public readonly ?int $statusCode = null,
        public readonly ?array $seo = null,
    ) {
    }

    public static function failed(string $error): self
    {
        return new self(ok: false, error: $error);
    }

    /**
     * @param array{h1: ?string, title: ?string, description: ?string} $seo
     */
    public static function succeeded(int $statusCode, array $seo): self
    {
        return new self(ok: true, statusCode: $statusCode, seo: $seo);
    }
}

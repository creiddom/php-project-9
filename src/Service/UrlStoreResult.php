<?php

declare(strict_types=1);

namespace App\Service;

use App\Validator\UrlValidationResult;

final class UrlStoreResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?int $urlId,
        public readonly string $flashMessage,
        public readonly ?UrlValidationResult $validation,
    ) {
    }

    public static function created(int $urlId, string $flashMessage): self
    {
        return new self(true, $urlId, $flashMessage, null);
    }

    public static function failed(UrlValidationResult $validation): self
    {
        return new self(false, null, '', $validation);
    }
}

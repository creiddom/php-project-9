<?php

declare(strict_types=1);

namespace App\Validator;

final class UrlValidationResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors,
        public readonly string $url,
    ) {
    }
}

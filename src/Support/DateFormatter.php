<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;

final class DateFormatter
{
    public static function formatCreatedAt(string $createdAt): string
    {
        return Carbon::parse($createdAt)->format('Y-m-d');
    }
}

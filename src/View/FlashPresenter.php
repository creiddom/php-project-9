<?php

declare(strict_types=1);

namespace App\View;

use Slim\Flash\Messages;

final class FlashPresenter
{
    /**
     * @return array<int, array{type: string, text: string}>
     */
    public static function forTemplate(Messages $flash): array
    {
        $messagesByType = $flash->getMessages();

        return array_merge(
            ...array_map(
                static fn (string $type, array $messages): array => array_map(
                    static fn (string $message): array => [
                        'type' => $type,
                        'text' => $message,
                    ],
                    $messages,
                ),
                array_keys($messagesByType),
                array_values($messagesByType),
            ),
        );
    }
}

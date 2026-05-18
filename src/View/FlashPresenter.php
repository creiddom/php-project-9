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
        $result = [];

        foreach ($flash->getMessages() as $type => $messages) {
            foreach ($messages as $message) {
                $result[] = [
                    'type' => $type,
                    'text' => $message,
                ];
            }
        }

        return $result;
    }
}

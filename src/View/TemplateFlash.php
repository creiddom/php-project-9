<?php

declare(strict_types=1);

namespace App\View;

use Countable;
use IteratorAggregate;
use Slim\Flash\Messages;
use Traversable;

/**
 * Flash-сообщения для шаблонов (читаются из session при рендере).
 *
 * @implements IteratorAggregate<int, array{type: string, text: string}>
 */
final class TemplateFlash implements IteratorAggregate, Countable
{
    public function __construct(
        private readonly Messages $messages,
    ) {
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator(FlashPresenter::forTemplate($this->messages));
    }

    public function count(): int
    {
        return count(FlashPresenter::forTemplate($this->messages));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use App\View\TemplateFlash;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;

final class TemplateFlashTest extends TestCase
{
    public function testIteratorAndCount(): void
    {
        $storage = [];
        $flash = new Messages($storage, 'template_flash_' . spl_object_id($this));
        $flash->addMessageNow('success', 'готово');

        $templateFlash = new TemplateFlash($flash);

        $this->assertCount(1, $templateFlash);
        $messages = iterator_to_array($templateFlash);

        $this->assertSame('success', $messages[0]['type']);
        $this->assertSame('готово', $messages[0]['text']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use App\View\FlashPresenter;
use PHPUnit\Framework\TestCase;
use Slim\Flash\Messages;

final class FlashPresenterTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $storage = [];

    protected function setUp(): void
    {
        $this->storage = [];
    }

    public function testForTemplateFlattensMessages(): void
    {
        $flash = new Messages($this->storage, 'flash_test_' . spl_object_id($this));
        $flash->addMessageNow('success', 'ok');
        $flash->addMessageNow('danger', 'fail');

        $result = FlashPresenter::forTemplate($flash);

        $this->assertCount(2, $result);
        $this->assertSame('success', $result[0]['type']);
        $this->assertSame('ok', $result[0]['text']);
        $this->assertSame('danger', $result[1]['type']);
    }

    public function testForTemplateReturnsEmptyArray(): void
    {
        $flash = new Messages($this->storage, 'flash_empty_' . spl_object_id($this));

        $this->assertSame([], FlashPresenter::forTemplate($flash));
    }
}

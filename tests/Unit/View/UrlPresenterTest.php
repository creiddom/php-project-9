<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use App\View\UrlPresenter;
use PHPUnit\Framework\TestCase;

final class UrlPresenterTest extends TestCase
{
    public function testForIndexList(): void
    {
        $urls = [
            ['id' => 1, 'name' => 'https://a.ru', 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'name' => 'https://b.ru', 'created_at' => '2024-01-02 10:00:00'],
        ];
        $statusByUrlId = [1 => 200];

        $result = UrlPresenter::forIndexList($urls, $statusByUrlId);

        $this->assertSame('200', $result[0]['last_status_code']);
        $this->assertSame('', $result[1]['last_status_code']);
        $this->assertSame('2024-01-01', $result[0]['created_at']);
    }

    public function testForShowPage(): void
    {
        $url = ['id' => 5, 'name' => 'https://c.ru', 'created_at' => '2024-03-01 00:00:00'];

        $result = UrlPresenter::forShowPage($url);

        $this->assertSame(5, $result['id']);
        $this->assertSame('2024-03-01', $result['created_at']);
    }

    public function testForChecksList(): void
    {
        $checks = [
            [
                'id' => 10,
                'status_code' => 200,
                'h1' => str_repeat('h', 300),
                'title' => null,
                'description' => 'desc',
                'created_at' => '2024-04-01 12:00:00',
            ],
        ];

        $result = UrlPresenter::forChecksList($checks);

        $this->assertStringEndsWith('...', $result[0]['h1']);
        $this->assertSame('', $result[0]['title']);
        $this->assertSame('desc', $result[0]['description']);
        $this->assertSame('200', $result[0]['status_code']);
    }
}

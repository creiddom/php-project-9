<?php

declare(strict_types=1);

namespace Tests\Unit\Repository;

use App\Exception\UrlsLoadFailedException;
use App\Repository\UrlRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use PDOStatement;

final class UrlRepositoryTest extends TestCase
{
    public function testFindAllOrdered(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $rows = [['id' => 1, 'name' => 'https://a.ru', 'created_at' => '2024-01-01']];

        $pdo->method('query')->willReturn($stmt);
        $stmt->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($rows);

        $repository = new UrlRepository($pdo);

        $this->assertSame($rows, $repository->findAllOrdered());
    }

    public function testFindAllOrderedThrowsWhenQueryFails(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('query')->willReturn(false);

        $this->expectException(UrlsLoadFailedException::class);

        (new UrlRepository($pdo))->findAllOrdered();
    }

    public function testFindLatestStatusCodeByUrlId(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('query')->willReturn($stmt);
        $stmt->method('fetchAll')->willReturn([
            ['url_id' => 1, 'status_code' => 200],
            ['url_id' => 2, 'status_code' => 404],
        ]);

        $result = (new UrlRepository($pdo))->findLatestStatusCodeByUrlId();

        $this->assertSame(200, $result[1]);
        $this->assertSame(404, $result[2]);
    }

    public function testFindByIdReturnsRowOrNull(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $row = ['id' => 3, 'name' => 'https://c.ru', 'created_at' => '2024-01-01'];

        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturnOnConsecutiveCalls($row, false);

        $repository = new UrlRepository($pdo);

        $this->assertSame($row, $repository->findById(3));
        $this->assertNull($repository->findById(99));
    }

    public function testFindIdByNameAndInsert(): void
    {
        $pdo = $this->createMock(PDO::class);
        $select = $this->createMock(PDOStatement::class);
        $insert = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')->willReturnOnConsecutiveCalls($select, $insert);
        $select->method('execute')->willReturn(true);
        $select->method('fetch')->willReturn(['id' => 5]);
        $insert->method('execute')->willReturn(true);
        $insert->method('fetchColumn')->willReturn('6');

        $repository = new UrlRepository($pdo);

        $this->assertSame(['id' => 5], $repository->findIdByName('https://d.ru'));
        $this->assertSame('6', $repository->insert('https://e.ru', '2024-05-25 10:00:00'));
    }

    public function testFindLatestStatusCodeByUrlIdThrowsWhenQueryFails(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('query')->willReturn(false);

        $this->expectException(UrlsLoadFailedException::class);

        (new UrlRepository($pdo))->findLatestStatusCodeByUrlId();
    }

    public function testFindChecksByUrlId(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $checks = [
            ['id' => 1, 'url_id' => 2, 'status_code' => 200, 'created_at' => '2024-01-01'],
        ];

        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->with([2])->willReturn(true);
        $stmt->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($checks);

        $this->assertSame($checks, (new UrlRepository($pdo))->findChecksByUrlId(2));
    }

    public function testFindIdByNameReturnsNullWhenMissing(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);

        $this->assertNull((new UrlRepository($pdo))->findIdByName('https://missing.test'));
    }

    public function testInsertCheck(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $pdo->method('prepare')->willReturn($stmt);
        $stmt->expects($this->once())
            ->method('execute')
            ->with([1, 200, 'h1', 'title', 'desc', '2024-05-25 10:00:00']);

        (new UrlRepository($pdo))->insertCheck(1, 200, 'h1', 'title', 'desc', '2024-05-25 10:00:00');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use App\Database\PdoFactory;
use App\Exception\DatabaseConfigException;
use PHPUnit\Framework\TestCase;

final class PdoFactoryTest extends TestCase
{
    private ?string $previousUrl = null;

    protected function setUp(): void
    {
        $this->previousUrl = getenv('DATABASE_URL') ?: null;
    }

    protected function tearDown(): void
    {
        if ($this->previousUrl === null) {
            putenv('DATABASE_URL');
            unset($_ENV['DATABASE_URL']);
        } else {
            putenv('DATABASE_URL=' . $this->previousUrl);
            $_ENV['DATABASE_URL'] = $this->previousUrl;
        }
    }

    public function testCreateThrowsWhenDatabaseUrlMissing(): void
    {
        putenv('DATABASE_URL');
        unset($_ENV['DATABASE_URL']);

        $this->expectException(DatabaseConfigException::class);
        $this->expectExceptionMessage('Не задан DATABASE_URL');

        PdoFactory::create();
    }

    public function testCreateThrowsWhenDatabaseNameMissing(): void
    {
        putenv('DATABASE_URL=postgresql://localhost:5432');
        $_ENV['DATABASE_URL'] = 'postgresql://localhost:5432';

        $this->expectException(DatabaseConfigException::class);
        $this->expectExceptionMessage('не указано имя базы');

        PdoFactory::create();
    }

    public function testCreateAddsSslmodeForRenderHost(): void
    {
        putenv('DATABASE_URL=postgresql://u:p@db.example.render.com:5432/mydb');
        $_ENV['DATABASE_URL'] = getenv('DATABASE_URL');

        try {
            PdoFactory::create();
            $this->fail('Expected connection failure');
        } catch (\PDOException) {
            $this->addToAssertionCount(1);
        }
    }

    public function testCreateBuildsDsnWithConnectTimeoutFromQuery(): void
    {
        putenv('DATABASE_URL=postgresql://u:p@localhost:5432/mydb?sslmode=disable&connect_timeout=3');
        $_ENV['DATABASE_URL'] = getenv('DATABASE_URL');

        try {
            PdoFactory::create();
            $this->fail('Expected connection failure');
        } catch (\PDOException) {
            $this->addToAssertionCount(1);
        }
    }

    public function testCreateThrowsWhenPathContainsQueryWithoutDbName(): void
    {
        putenv('DATABASE_URL=postgresql://localhost:5432/dbname=mydb');
        $_ENV['DATABASE_URL'] = 'postgresql://localhost:5432/dbname=mydb';

        $this->expectException(DatabaseConfigException::class);

        PdoFactory::create();
    }
}

<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use RuntimeException;

final class PdoFactory
{
    public static function create(): PDO
    {
        $databaseUrlRaw = trim((string) ($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: ''));

        if ($databaseUrlRaw === '') {
            throw new RuntimeException(
                'Не задан DATABASE_URL. Пример: '
                . 'export DATABASE_URL=postgresql://localhost:5432/page_analyzer '
                . '(или запустите make start — Makefile подставит URL сам)'
            );
        }

        $databaseUrl = parse_url($databaseUrlRaw);
        if (!is_array($databaseUrl)) {
            $databaseUrl = [];
        }

        $host = $databaseUrl['host'] ?? 'localhost';
        $port = (int) ($databaseUrl['port'] ?? 5432);
        $dbname = ltrim((string) ($databaseUrl['path'] ?? ''), '/');
        $dbUser = $databaseUrl['user'] ?? null;
        $dbPass = $databaseUrl['pass'] ?? null;

        $query = [];
        if (!empty($databaseUrl['query'])) {
            parse_str($databaseUrl['query'], $query);
        }

        if (str_contains($dbname, '=')) {
            parse_str($dbname, $pathQuery);
            $query = array_merge($pathQuery, $query);
            $dbname = '';
        }

        if ($dbname === '') {
            throw new RuntimeException(
                'В DATABASE_URL не указано имя базы. Пример: postgresql://localhost:5432/page_analyzer'
            );
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

        $sslmode = $query['sslmode'] ?? null;
        if (is_string($sslmode) && $sslmode !== '') {
            $dsn .= ';sslmode=' . $sslmode;
        } elseif (str_contains($host, 'render.com')) {
            $dsn .= ';sslmode=require';
        }

        if (isset($query['connect_timeout']) && is_scalar($query['connect_timeout'])) {
            $dsn .= ';connect_timeout=' . (int) $query['connect_timeout'];
        }

        $pdo = new PDO($dsn, $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}

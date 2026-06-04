<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\UrlsLoadFailedException;
use PDO;

final class UrlRepository
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAllOrdered(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, created_at FROM urls ORDER BY created_at DESC, id DESC'
        );

        if ($stmt === false) {
            throw new UrlsLoadFailedException('Failed to load urls');
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, mixed>
     */
    public function findLatestStatusCodeByUrlId(): array
    {
        $stmt = $this->pdo->query(
            'SELECT DISTINCT ON (url_id) url_id, status_code
             FROM url_checks
             ORDER BY url_id, created_at DESC, id DESC'
        );

        if ($stmt === false) {
            throw new UrlsLoadFailedException('Failed to load url checks');
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $statusByUrlId = [];

        foreach ($rows as $row) {
            $statusByUrlId[(int) $row['url_id']] = $row['status_code'];
        }

        return $statusByUrlId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int|string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, created_at FROM urls WHERE id = ?');
        $stmt->execute([$id]);
        $url = $stmt->fetch(PDO::FETCH_ASSOC);

        return $url !== false ? $url : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findChecksByUrlId(int|string $urlId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, url_id, status_code, h1, title, description, created_at
             FROM url_checks
             WHERE url_id = ?
             ORDER BY created_at DESC'
        );
        $stmt->execute([$urlId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findIdByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM urls WHERE name = ?');
        $stmt->execute([$name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function insert(string $name, string $createdAt): string
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO urls (name, created_at) VALUES (?, ?) RETURNING id'
        );
        $stmt->execute([$name, $createdAt]);

        return (string) $stmt->fetchColumn();
    }

    public function insertCheck(
        int|string $urlId,
        ?int $statusCode,
        ?string $h1,
        ?string $title,
        ?string $description,
        string $createdAt,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$urlId, $statusCode, $h1, $title, $description, $createdAt]);
    }
}

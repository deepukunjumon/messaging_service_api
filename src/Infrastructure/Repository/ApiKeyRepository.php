<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PDO;
use Ramsey\Uuid\Uuid;

final class ApiKeyRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $clientId): string
    {
        $uuid = Uuid::uuid4()->toString();
        $apiKey = bin2hex(random_bytes(32)); // 64 chars

        $stmt = $this->pdo->prepare("
            INSERT INTO api_keys (id, client_id, api_key)
            VALUES (:id, :client_id, :api_key)
        ");

        $stmt->execute([
            'id' => $uuid,
            'client_id' => $clientId,
            'api_key' => $apiKey
        ]);

        return $apiKey;
    }

    public function validate(string $apiKey): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name
            FROM api_keys k
            JOIN api_clients c ON k.client_id = c.id
            WHERE k.api_key = :api_key
            AND k.status = 1
            AND c.status = 1
            AND (k.expires_at IS NULL OR k.expires_at > NOW())
        ");

        $stmt->execute(['api_key' => $apiKey]);

        return $stmt->fetch() ?: null;
    }
}
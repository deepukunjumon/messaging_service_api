<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\ApiKey;

use App\Domain\ApiKey\ApiKeyRepositoryInterface;

use PDO;
use Ramsey\Uuid\Uuid;

final class ApiKeyRepository implements ApiKeyRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /*
     * {@inheritDoc}
     */
    public function create(string $clientId): string
    {
        try {
            $uuid = Uuid::uuid4()->toString();
            $apiKey = bin2hex(random_bytes(32)); // 64 chars

            $sql = "INSERT INTO api_keys (id, client_id, api_key)
                    VALUES (:id, :client_id, :api_key)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':id', $uuid);
            $stmt->bindValue(':client_id', $clientId);
            $stmt->bindValue(':api_key', $apiKey);
            $result = $stmt->execute();
            if (!$result) {
                throw new \RuntimeException("Failed to create API key");
            }
            return $apiKey;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /*
     * {@inheritDoc}
     */
    public function validate(string $apiKey): ?array
    {
        try {
            $sql = "SELECT c.id, c.name
                    FROM api_keys k
                    JOIN api_clients c ON k.client_id = c.id
                    WHERE k.api_key = :api_key
                    AND k.status = 1
                    AND c.status = 1
                    AND (k.expires_at IS NULL OR k.expires_at > NOW())";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':api_key', $apiKey);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }
}
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

    /**
     * {@inheritDoc}
     */
    public function updateStatus(string $apiKeyId, int $status): bool
    {
        try {
            $sql = "UPDATE api_keys
                    SET status = :status,
                        updated_at = NOW()
                    WHERE id = :id";
                
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':status', $status, \PDO::PARAM_INT);
            $stmt->bindParam(':id', $apiKeyId, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            $this->logger->error('Failed to update api key status', [
                'apiKeyId' => $apiKeyId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateStatus(string $clientId, int $status): bool
    {
        try {
            $sql = "UPDATE api_keys
                    SET status = :status,
                        updated_at = NOW()
                    WHERE client_id = :clientId";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':status', $status, \PDO::PARAM_INT);
            $stmt->bindParam(':clientId', $clientId, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            $this->logger->error('Failed to update api key status', [
                'clientId' => $clientId,
                'error' => $e->getMessage()
            ]);

            return false;
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

    /**
     * {@inheritDoc}
     */
    public function getClientsApiKeys(string $clientId, ?string $q): array
    {
        try {
            $sql = "SELECT
                        ak.id,
                        ak.api_key,
                        ak.status,
                        ak.expires_at,
                        ak.created_at,
                        ak.updated_at,
                        ak.client_id AS api_client_id,
                        ac.name AS api_client_name
                    FROM api_keys ak
                    INNER JOIN api_clients ac 
                        ON ak.client_id = ac.id
                    WHERE ac.id = :clientId";

            $params = [];
            if ($q) {
                $sql .= " AND (
                            ak.api_key LIKE :q1
                        )";
                
                $params[':q1'] = "%$q%";
            }

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':clientId', $clientId, PDO::PARAM_STR);
            foreach ($params as $key => $value) $stmt->bindValue($key, $value);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }
}
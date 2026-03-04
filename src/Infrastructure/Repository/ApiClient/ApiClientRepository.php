<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\ApiClient;

use App\Domain\ApiClient\ApiClientRepositoryInterface;

use PDO;
use Ramsey\Uuid\Uuid;

final class ApiClientRepository implements ApiClientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $name, ?string $description = null): array
    {
        try {
            $uuid = Uuid::uuid4()->toString();

            $stmt = $this->pdo->prepare("
                INSERT INTO api_clients (id, name, description)
                VALUES (:id, :name, :description)
            ");

            $stmt->execute([
                'id' => $uuid,
                'name' => $name,
                'description' => $description
            ]);

            $result = [
                'id' => $uuid,
                'name' => $name,
            ];

            return $result;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function updateStatus(string $clientId, int $status): bool
    {
        try {
            $sql = "UPDATE api_clients
                    SET status = :status,
                        updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':status', $status, \PDO::PARAM_INT);
            $stmt->bindParam(':id', $clientId, \PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (\Throwable $e) {
            $this->logger->error('Failed to update client status', [
                'clientId' => $clientId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM api_clients WHERE id = :id
        ");

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(?string $q, ?string $sortKey, ?string $sortOrder, int $offset, int $limit, ?int $status = null): array
    {
        $allowedSortKeys = ['id', 'name', 'description'];
        $allowedSortOrders = ['ASC', 'DESC'];

        $sortKey = in_array($sortKey, $allowedSortKeys) ? $sortKey : 'name';
        $sortOrder = strtoupper($sortOrder);
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'ASC';

        $sql = "SELECT 
                    id, name, description, status, created_at, updated_at
                FROM api_clients
                WHERE 1=1";

        $params = [];

        if ($q) {
            $sql .= " AND (name LIKE :q1 OR description LIKE :q2)";
            $params[':q1'] = "%$q%";
            $params[':q2'] = "%$q%";
        }

        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY $sortKey $sortOrder LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            if ($status) {
                $stmt->bindValue(':status', $status, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, \PDO::PARAM_STR);
            }
        }

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
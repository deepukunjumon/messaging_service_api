<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PDO;
use Ramsey\Uuid\Uuid;

final class ApiClientRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $name, ?string $description = null): string
    {
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

        return $uuid;
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM api_clients WHERE id = :id
        ");

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }
}
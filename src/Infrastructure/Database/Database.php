<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use Throwable;

final class Database
{
    private PDO $connection;

    public function __construct(array $config)
    {
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        try {
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException(
                'Database connection failed: ' . $e->getMessage()
            );
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // ---------------------------
    // Manual Transaction Control
    // ---------------------------

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }

    // ---------------------------
    // Automatic Transaction Wrapper
    // ---------------------------

    public function transaction(callable $callback)
    {
        try {
            $this->beginTransaction();

            $result = $callback($this->connection);

            $this->commit();

            return $result;

        } catch (Throwable $e) {

            $this->rollBack();

            throw $e;
        }
    }
}
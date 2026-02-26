<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\OutgoingMessage;

use App\Domain\OutgoingMessage\OutgoingMessageRepositoryInterface;
use PDO;
use Ramsey\Uuid\Uuid;

final class OutgoingMessageRepository implements OutgoingMessageRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $clientId, ?string $apiKeyId, string $channel, string $recipient, ?string $subject, string $body, string $provider, ?array $metadata = null): string
    {
        try {

            $id = Uuid::uuid4()->toString();

            $sql = "INSERT INTO outgoing_messages (id, client_id, api_key_id, channel, recipient, subject, body, provider, metadata, status) 
                        VALUES (:id, :client_id, :api_key_id, :channel, :recipient, :subject, :body, :provider, :metadata, 'queued')";
            
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':client_id', $clientId);
            $stmt->bindValue(':api_key_id', $apiKeyId);
            $stmt->bindValue(':channel', $channel);
            $stmt->bindValue(':recipient', $recipient);
            $stmt->bindValue(':subject', $subject);
            $stmt->bindValue(':body', $body);
            $stmt->bindValue(':provider', $provider);
            $stmt->bindValue(':metadata', $metadata ? json_encode($metadata) : null);
            $result = $stmt->execute();

            if (!$result) {
                throw new \RuntimeException("Failed to create outgoing message");
            }
            return $id;

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function markSent(string $messageId, ?string $providerMessageId = null): bool
    {
        try {
             $sql = "UPDATE outgoing_messages 
                        SET status = 'sent', provider_message_id = :provider_message_id, sent_at = NOW(), attempts = attempts + 1 
                        WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $messageId);
            $stmt->bindValue(':provider_message_id', $providerMessageId);
            return $stmt->execute();

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function markFailed(string $messageId, string $errorMessage): bool
    {
        try {
             $sql = "UPDATE outgoing_messages 
                        SET status = 'failed', error_message = :error_message, attempts = attempts + 1 
                        WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $messageId);
            $stmt->bindValue(':error_message', $errorMessage);
            return $stmt->execute();

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(string $from, string $to, string $channel, string $apiClientId): array
    {
        try {
            $sql = "SELECT * FROM outgoing_messages 
                    WHERE created_at BETWEEN :from AND :to 
                    AND channel = :channel 
                    AND api_client_id = :api_client_id
                    ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':from', $from);
            $stmt->bindValue(':to', $to);
            $stmt->bindValue(':channel', $channel);
            $stmt->bindValue(':api_client_id', $apiClientId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getById(string $messageId): ?array
    {
        try {
            $sql = "SELECT * FROM outgoing_messages WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $messageId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAllByClientId(string $clientId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM outgoing_messages WHERE client_id = :client_id ORDER BY created_at DESC");
            $stmt->bindValue(':client_id', $clientId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAllByApiKeyId(string $apiKeyId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM outgoing_messages WHERE api_key_id = :api_key_id ORDER BY created_at DESC");
            $stmt->bindValue(':api_key_id', $apiKeyId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        }
    }
}
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
    public function getAll(string $q, string $sortKey, string $sortOrder, int $offset, int $limit, ?string $startDate, ?string $endDate, ?string $channel, ?string $clientId): array
    {
        try {
            $sql = "SELECT 
                        outgoing_messages.id,
                        outgoing_messages.client_id,
                        api_clients.name AS api_client,
                        outgoing_messages.channel,
                        outgoing_messages.recipient,
                        outgoing_messages.subject,
                        outgoing_messages.body,
                        outgoing_messages.provider,
                        outgoing_messages.metadata,
                        outgoing_messages.status,
                        outgoing_messages.created_at
                    FROM outgoing_messages
                    LEFT JOIN api_clients ON outgoing_messages.client_id = api_clients.id
                    WHERE 1=1";

            $params = [];
            if ($startDate) { 
                $sql .= " AND outgoing_messages.created_at >= :start_date"; 
                $params[':start_date'] = $startDate;
            }
            if ($endDate) { 
                $sql .= " AND outgoing_messages.created_at <= :end_date"; 
                $params[':end_date'] = $endDate;
            }
            if ($channel) { 
                $sql .= " AND outgoing_messages.channel = :channel"; 
                $params[':channel'] = $channel;
            }
            if ($clientId) { 
                $sql .= " AND outgoing_messages.client_id = :client_id"; 
                $params[':client_id'] = $clientId;
            }
            if ($q) { 
                $sql .= " AND (outgoing_messages.recipient LIKE :q1
                            OR outgoing_messages.subject LIKE :q2
                            OR outgoing_messages.body LIKE :q3)"; 
                $params[':q1'] = "%$q%";
                $params[':q2'] = "%$q%";
                $params[':q3'] = "%$q%";
            }

            $allowedSortKeys = ['created_at', 'id', 'channel'];
            $allowedSortOrder = ['asc', 'desc'];
            if (!in_array($sortKey, $allowedSortKeys)) $sortKey = 'created_at';
            if (!in_array(strtolower($sortOrder), $allowedSortOrder)) $sortOrder = 'desc';

            $sql .= " ORDER BY {$sortKey} {$sortOrder} LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);

            foreach ($params as $key => $value) $stmt->bindValue($key, $value);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
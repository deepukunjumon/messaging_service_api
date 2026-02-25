<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PDO;
use Ramsey\Uuid\Uuid;

final class OutgoingMessageRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(
        string $clientId,
        ?string $apiKeyId,
        string $channel,
        string $recipient,
        ?string $subject,
        string $body,
        string $provider,
        ?array $metadata = null
    ): string {

        $id = Uuid::uuid4()->toString();

        $stmt = $this->pdo->prepare("
            INSERT INTO outgoing_messages (
                id,
                client_id,
                api_key_id,
                channel,
                recipient,
                subject,
                body,
                provider,
                metadata,
                status
            ) VALUES (
                :id,
                :client_id,
                :api_key_id,
                :channel,
                :recipient,
                :subject,
                :body,
                :provider,
                :metadata,
                'queued'
            )
        ");

        $stmt->execute([
            'id'         => $id,
            'client_id'  => $clientId,
            'api_key_id' => $apiKeyId,
            'channel'    => $channel,
            'recipient'  => $recipient,
            'subject'    => $subject,
            'body'       => $body,
            'provider'   => $provider,
            'metadata'   => $metadata ? json_encode($metadata) : null,
        ]);

        return $id;
    }

    public function markSent(
        string $messageId,
        ?string $providerMessageId = null
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE outgoing_messages
            SET status = 'sent',
                provider_message_id = :provider_message_id,
                sent_at = NOW(),
                attempts = attempts + 1
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $messageId,
            'provider_message_id' => $providerMessageId,
        ]);
    }

    public function markFailed(
        string $messageId,
        string $errorMessage
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE outgoing_messages
            SET status = 'failed',
                error_message = :error_message,
                attempts = attempts + 1
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $messageId,
            'error_message' => $errorMessage,
        ]);
    }
}
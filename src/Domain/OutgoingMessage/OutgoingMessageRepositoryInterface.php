<?php

declare(strict_types=1);

namespace App\Domain\OutgoingMessage;

interface OutgoingMessageRepositoryInterface
{
    /**
     * Create a new outgoing message.
     *
     * @param string $clientId
     * @param string|null $apiKeyId
     * @param string $channel
     * @param string $recipient
     * @param string|null $subject
     * @param string $body
     * @param string $provider
     * @param array|null $metadata
     * @return string The ID of the created message
     */
    public function create(string $clientId, ?string $apiKeyId, string $channel, string $recipient, ?string $subject, string $body, string $provider, ?array $metadata = null): string;
    
    /**
     * Mark a message as sent.
     *
     * @param string $messageId
     * @param string|null $providerMessageId
     * @return bool
     */
    public function markSent(string $messageId, ?string $providerMessageId = null): bool;

    /**
     * Mark a message as failed.
     *
     * @param string $messageId
     * @param string $errorMessage
     * @return void
     */
    public function markFailed(string $messageId, string $errorMessage): bool;

    /**
     * Get all messages for a given time range, channel, and API client ID.
     *
     * @param string $q
     * @param string $sortKey
     * @param string $sortOrder
     * @param int $offset
     * @param int $limit
     * @param ?string $startDate
     * @param ?string $endDate
     * @param ?string $channel
     * @param ?string $clientId
     * 
     * @return array
     */
    public function getAll(string $q, string $sortKey, string $sortOrder, int $offset, int $limit, ?string $startDate, ?string $endDate, ?string $channel, ?string $clientId): array;

    /**
     * Get a message details by its ID.
     *
     * @param string $messageId
     * @return array|null
     */
    public function getById(string $messageId): ?array;

    /**
    * Get all messages for a given client ID.
    *
    * @param string $clientId
    * @return array
    */
    public function getAllByClientId(string $clientId): array;

    /**
     * Get all messages for a given API key ID.
     *
     * @param string $apiKeyId
     * @return array
     */
    public function getAllByApiKeyId(string $apiKeyId): array;
}

<?php

declare(strict_types=1);

namespace App\Domain\ApiKey;

interface ApiKeyRepositoryInterface
{
    /**
     * Create a new API key for a given client ID.
     *
     * @param string $clientId
     * @return string The generated API key.
     */
    public function create(string $clientId): string;

    /**
     * Update API Key status
     * 
     * @param string $apiKey
     * @param int $status
     * 
     * @return bool
     */
    public function updateStatus(string $apiKey, int $status): bool;

    /**
     * Bulk update API Key status for a given client Id
     * 
     * @param string $clientId
     * @param int $status
     * 
     * @return bool
     */
    public function bulkUpdateStatus(string $clientId, int $status): bool;

    /**
    * Validate an API key and return the associated client information if valid.
    *
    * @param string $apiKey
    * @return array|null
    */
    public function validate(string $apiKey): ?array;

    /**
     * Get All API Keys generated for a client
     * 
     * @param string $clientId
     * @return array
     */
    public function getClientsApiKeys(string $clientId): array;
}

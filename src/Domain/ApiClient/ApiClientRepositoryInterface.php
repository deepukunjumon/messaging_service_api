<?php

declare(strict_types=1);

namespace App\Domain\ApiClient;

interface ApiClientRepositoryInterface
{
    /**
     * Creates a new API client
     *
     * @param string $name
     * @param string|null $description
     * 
     * @return array
     */
    public function create(string $name, ?string $description = null): array;

    /**
     * Update client status
     * 
     * @param string $clientId
     * @param int $status
     * 
     * @return bool
     */
    public function updateStatus(string $clientId, int $status): bool;

    /**
     * Update Client Details
     * 
     * @param string $clientId
     * @param array $details
     * 
     * @return bool
     */
    public function updateClientDetails(string $clientId, array $details): bool;

    /**
     * Finds an API client by ID.
     *
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array;

    /**
     * Retrieve All API clients
     *
     * @param string|null $q
     * @param string|null $sortKey
     * @param string|null $sortOrder
     * @param int $limit
     * @param int $offset
     * @param int|null $status
     *
     * @return array
     */
    public function findAll(?string $q, ?string $sortKey, ?string $sortOrder, int $offset, int $limit, ?int $status = null): array;

    /**
     * Retrieve active API clients with minimal details
     *
     * @param string|null $q
     * @return array
     */
    public function findActiveMinimal(?string $q): array;
}

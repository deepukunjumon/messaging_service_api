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
     * @return string
     */
    public function create(string $name, ?string $description = null): string;

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
     *
     * @return array
     */
    public function findAll(?string $q, ?string $sortKey, ?string $sortOrder, int $offset, int $limit): array;
}

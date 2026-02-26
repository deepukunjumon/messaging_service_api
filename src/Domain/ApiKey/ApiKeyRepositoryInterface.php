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
    * Validate an API key and return the associated client information if valid.
    *
    * @param string $apiKey
    * @return array|null
    */
    public function validate(string $apiKey): ?array;
}

<?php

declare(strict_types=1);

namespace App\Domain\ApiClient;

interface ApiClientRepositoryInterface
{
    public function create(string $name, ?string $description = null): string;
    public function findById(string $id): ?array;
}

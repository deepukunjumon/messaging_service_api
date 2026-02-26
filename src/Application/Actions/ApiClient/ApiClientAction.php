<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use App\Application\Actions\Action;
use App\Infrastructure\Database\Database;
use App\Domain\ApiClient\ApiClientRepositoryInterface;
use App\Domain\ApiKey\ApiKeyRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class ApiClientAction extends Action
{
    protected Database $database;
    protected ApiClientRepositoryInterface $apiClientRepo;
    protected ApiKeyRepositoryInterface $apiKeyRepo;

    public function __construct(LoggerInterface $logger, Database $database, ApiClientRepositoryInterface $apiClientRepo, ApiKeyRepositoryInterface $apiKeyRepo)
    {
        parent::__construct($logger);
        $this->database = $database;
        $this->apiKeyRepo = $apiKeyRepo;
        $this->apiClientRepo = $apiClientRepo;
    }
}
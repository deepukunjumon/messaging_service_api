<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use App\Infrastructure\Repository\ApiKeyRepository;
use App\Infrastructure\Repository\ApiClientRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GenerateApiKeyAction
{
    private ApiKeyRepository $apiKeyRepo;
    private ApiClientRepository $clientRepo;

    public function __construct(
        ApiKeyRepository $apiKeyRepo,
        ApiClientRepository $clientRepo
    ) {
        $this->apiKeyRepo = $apiKeyRepo;
        $this->clientRepo = $clientRepo;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {

        $clientId = $args['id'];

        $client = $this->clientRepo->findById($clientId);

        if (!$client) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Client not found'
            ]));

            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }

        $apiKey = $this->apiKeyRepo->create($clientId);

        $response->getBody()->write(json_encode([
            'success' => true,
            'api_key' => $apiKey
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
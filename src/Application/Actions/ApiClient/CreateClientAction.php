<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use App\Infrastructure\Repository\ApiClientRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class CreateClientAction
{
    private ApiClientRepository $apiClientRepo;

    public function __construct(
        ApiClientRepository $apiClientRepo
    ) {
        $this->apiClientRepo = $apiClientRepo;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        try {

            $data = $request->getParsedBody() ?? [];

            // Validation
            if (empty($data['clientName']) || !is_string($data['clientName'])) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Client name is required'
                ], 422);
            }

            $clientId = $this->apiClientRepo->create(
                trim($data['clientName']),
                $data['description'] ?? null
            );

            return $this->json($response, [
                'success' => true,
                'client_id' => $clientId
            ], 201);

        } catch (Throwable $e) {

            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to create client'
            ], 500);
        }
    }

    private function json(
        ResponseInterface $response,
        array $data,
        int $status = 200
    ): ResponseInterface {

        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
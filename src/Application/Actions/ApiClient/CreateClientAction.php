<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Throwable;
use App\Infrastructure\Validation\ValidationUtil;

final class CreateClientAction extends ApiClientAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $data = $this->getFormData();

        // Sanitize Input
        $input = [
            'clientName' => trim((string)($data['clientName'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
        ];

        // Validation
        $rules = [
            'clientName' => [
                'rule' => v::notEmpty()->stringType()->length(3, 255),
                'message' => 'Name is required and must be between 3 and 255 characters'
            ],
            'description' => [
                'rule' => v::optional(v::stringType()->length(0, 500)),
                'message' => 'Description must be less than 500 characters'
            ],
        ];

        $errors = ValidationUtil::validate($input, $rules);

        if (!empty($errors)) {
            return $this->respondWithError(
                'Validation failed',
                422,
                $errors
            );
        }

        try {
            $client = $this->database->transaction(function () use ($input) {
                // Create Client
                $createClient = $this->apiClientRepo->create($input['clientName'], $input['description']);
                if (!$createClient) {
                    throw new \RuntimeException("Failed to create client");
                }
                // Generate API Key
                $generateApiKey = $this->apiKeyRepo->create($createClient['id']);
                if (!$generateApiKey) {
                    throw new \RuntimeException("Failed to create API key");
                }
                return [
                    'clientId' => $createClient['id'],
                    'apiKey' => $generateApiKey
                ];
            });

            return $this->respondWithData([
                'message' => 'Client created successfully',
                'clientId' => $client['clientId'],
                'apiKey' => $client['apiKey']
            ], 201);
        } catch (Throwable $e) {
            $this->logger->error('Failed to create API client', ['error' => $e->getMessage()]);

            return $this->respondWithError(
                'Failed to create API client',
                500
            );
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use App\Infrastructure\Validation\ValidationUtil;

final class GenerateApiKeyAction extends ApiClientAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $clientId = $this->resolveArg('clientId');

        $input = [
            'clientId' => $clientId,
        ];

        // Validation rules
        $rules = [
            'clientId' => [
                'rule' => v::notEmpty()->stringType()->length(3, 255),
                'message' => 'Invalid clientId format'
            ],
        ];

        $errors = ValidationUtil::validate($input, $rules);

        if ($errors) {
            return $this->respondWithError(
                'Validation failed',
                400,
                $errors
            );
        }

        // Check if client exists
        $client = $this->apiClientRepo->findById($clientId);

        if (!$client) {
            return $this->respondWithError(
                'Client not found',
                404
            );
        }

        // Create API key
        $result = $this->database->transaction(function () use ($clientId) {
            $apiKey = $this->apiKeyRepo->create($clientId);

            return [
                'success' => true,
                'apiKey'  => $apiKey
            ];
        });

        return $this->respondWithData($result, 201);
    }
}
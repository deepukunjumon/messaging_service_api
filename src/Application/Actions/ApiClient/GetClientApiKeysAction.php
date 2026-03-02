<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use Psr\Http\Message\ResponseInterface as Response;
use Throwable;
use Respect\Validation\Validator as v;
use App\Infrastructure\Validation\ValidationUtil;

final class GetClientApiKeysAction extends ApiClientAction
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

        try {

            $apiKeys = $this->apiKeyRepo->getClientsApiKeys($clientId);

            return $this->respondWithData($apiKeys, 200);
        }
        catch (Throwable $e) {
            $this->logger->error('Failed to fetch API keys', ['error' => $e->getMessage()]);

            return $this->respondWithError(
                'An error occurred while fetching API keys',
                500
            );
        }
    }
}
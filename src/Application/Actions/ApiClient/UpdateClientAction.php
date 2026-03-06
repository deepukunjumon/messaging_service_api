<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Throwable;
use App\Infrastructure\Validation\ValidationUtil;

final class UpdateClientAction extends ApiClientAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $clientId = $this->resolveArg('clientId');
        
        $data = $this->getFormData();

        $input = [
            'clientName' => trim((string)($data['clientName'] ?? null)),
            'description' => trim((string)($data['description'] ?? null)),
            'status' => isset($data['status']) ? (int)$data['status'] : null,
        ];

        $rules = [
            'clientName' => [
                'rule' => v::optional()->stringType()->length(3, 255),
                'message' => 'Name must be between 3 and 255 characters'
            ],
            'description' => [
                'rule' => v::optional(v::stringType()->length(0, 500)),
                'message' => 'Description must be less than 500 characters'
            ],
            'status' => [
                'rule' => v::optional()->intVal()->in([0, 1, -1]),
                'message' => 'Invalid status value',
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

                $updateClient = $this->apiClientRepo->updateClientDetails($clientId, $input);
            });

            return $this->respondWithData([
                'message' => 'Details updated',
                'clientId' => $client['clientId'],
            ], 200);
        } catch (Throwable $e) {
            $this->logger->error('Failed to update client details', ['error' => $e->getMessage()]);

            return $this->respondWithError(
                'Failed to update client details',
                500
            );
        }
    }
}
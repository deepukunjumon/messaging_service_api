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

        $input = [];

        if (isset($data['clientName'])) {
            $input['name'] = trim((string) $data['clientName']);
        }

        if (isset($data['description'])) {
            $input['description'] = trim((string) $data['description']);
        }

        if (isset($data['status'])) {
            $input['status'] = (int) $data['status'];
        }

        $rules = [
            'name' => [
                'rule' => v::optional(v::stringType()->length(3, 255)),
                'message' => 'Name must be between 3 and 255 characters'
            ],
            'description' => [
                'rule' => v::optional(v::stringType()->length(0, 500)),
                'message' => 'Description must be less than 500 characters'
            ],
            'status' => [
                'rule' => v::optional(v::intVal()->in([0, 1, -1])),
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

        if (empty($input)) {
            return $this->respondWithError(
                'No data provided to update',
                400
            );
        }

        try {

            $this->database->transaction(function () use ($clientId, $input) {
                $this->apiClientRepo->updateClientDetails($clientId, $input);
            });

            return $this->respondWithData([
                'message' => 'Details updated',
                'clientId' => $clientId,
            ], 200);

        } catch (Throwable $e) {

            $this->logger->error('Failed to update client details', [
                'clientId' => $clientId,
                'error' => $e->getMessage()
            ]);

            return $this->respondWithError(
                'Failed to update client details',
                500
            );
        }
    }
}
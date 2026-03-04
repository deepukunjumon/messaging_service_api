<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Throwable;
use App\Infrastructure\Validation\ValidationUtil;

final class UpdateApiKeyStatusAction extends ApiClientAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $apiKeyId = $this->resolveArg('apiKeyId');

        $data = $this->getFormData();

        $input = [
            'status' => isset($data['status']) ? (int)$data['status'] : null,
        ];

        $rules = [
            'status' => [
                'rule' => v::intVal()->in([0, 1, -1]),
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
            $this->database->transaction(function () use ($apiKeyId, $input) {

                $updateApiKeyStatus = $this->apiKeyRepo->updateStatus($apiKeyId, $input['status']);
                return true;
            });

            return $this->respondWithData([
                'message' => 'Status updated successfully'
            ], 200);

        } catch (Throwable $e) {

            $this->logger->error(
                'Failed to update api key status: ' . $apiKeyId,
                ['error' => $e->getMessage()]
            );

            return $this->respondWithError(
                'Failed to update api key status',
                500
            );
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;

use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Throwable;
use App\Infrastructure\Validation\ValidationUtil;

final class UpdateClientStatusAction extends ApiClientAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $clientId = $this->resolveArg('clientId');
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
            $this->database->transaction(function () use ($clientId, $input) {

                // Update client status
                $updated = $this->apiClientRepo->updateStatus(
                    $clientId,
                    $input['status']
                );

                if (!$updated) {
                    throw new \RuntimeException("Client not found or update failed");
                }

                // If disabling permanently (-1), revoke all keys
                if ($input['status'] === -1) {
                    $this->apiKeyRepo->bulkUpdateStatus(
                        $clientId,
                        -1
                    );
                }

                return true;
            });

            return $this->respondWithData([
                'message' => 'Client status updated successfully'
            ], 200);

        } catch (Throwable $e) {

            $this->logger->error(
                'Failed to update client status: ' . $clientId,
                ['error' => $e->getMessage()]
            );

            return $this->respondWithError(
                'Failed to update client status',
                500
            );
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;
use App\Infrastructure\Export\CsvExporter;

use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

final class GetMinimalApiClientsAction extends ApiClientAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $params = $this->request->getQueryParams();

        $q = isset($params['q']) ? trim((string)$params['q']) : '';
        $sortKey = isset($params['sortKey']) ? trim((string)$params['sortKey']) : 'created_at';
        $sortOrder = isset($params['sortOrder']) ? trim((string)$params['sortOrder']) : 'desc';
        
        try {

            $clients = $this->apiClientRepo->findActiveMinimal($q);

            return $this->respondWithData($clients, 200);
        }
        catch (Throwable $e) {
            $this->logger->error('Failed to fetch API clients', ['error' => $e->getMessage()]);

            return $this->respondWithError(
                'An error occurred while fetching API clients',
                500
            );
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Application\Actions\ApiClient;
use App\Infrastructure\Export\CsvExporter;

use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

final class GetAllApiClientsAction extends ApiClientAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $params = $this->request->getQueryParams();

        $q = isset($params['q']) ? trim((string)$params['q']) : '';
        $sortKey = isset($params['sort_key']) ? trim((string)$params['sort_key']) : 'created_at';
        $sortOrder = isset($params['sort_order']) ? trim((string)$params['sort_order']) : 'desc';
        $limit = isset($params['limit']) ? (int)$params['limit'] : 100;
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;

        $export = isset($params['export']) && filter_var($params['export'], FILTER_VALIDATE_BOOLEAN);
        $type   = isset($params['type']) ? strtolower(trim((string)$params['type'])) : null;

        try {

            $clients = $this->apiClientRepo->findAll($q, $sortKey, $sortOrder, $offset, $limit);
            
            if ($export && $type === 'csv') {

                $columns = [
                    'id'          =>  'Client ID',
                    'name'        => 'Name',
                    'description' => 'Description',
                    'created_at'  => 'Created At',
                    'updated_at'  => 'Updated At'
                ];

                $exporter = new CsvExporter();
                $csv = $exporter->generate($clients, $columns);

                $filename = 'API_Clients' . date('Y-m-d') . '.csv';

                $response = $this->response
                    ->withHeader('Content-Type', 'text/csv')
                    ->withHeader('Content-Disposition', "attachment; filename=\"$filename\"");

                $response->getBody()->write($csv);

                return $response;
            }

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
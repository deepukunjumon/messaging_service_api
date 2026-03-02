<?php

declare(strict_types=1);

namespace App\Application\Actions\OutgoingMessage;

use Psr\Http\Message\ResponseInterface as Response;
use App\Infrastructure\Export\CsvExporter;

final class GetAllOutgoingMessagesAction extends OutgoingMessageAction
{
    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $params = $this->request->getQueryParams();

        $q         = isset($params['q']) ? trim((string)$params['q']) : '';
        $startDate = isset($params['startDate']) ? trim((string)$params['startDate']) : null;
        $endDate   = isset($params['endDate']) ? trim((string)$params['endDate']) : null;
        $channel   = isset($params['channel']) ? trim((string)$params['channel']) : null;
        $clientId  = isset($params['client_id']) ? trim((string)$params['client_id']) : null;

        $sortKey   = isset($params['sort_key']) ? trim((string)$params['sort_key']) : 'created_at';
        $sortOrder = isset($params['sort_order']) ? strtolower(trim((string)$params['sort_order'])) : 'desc';

        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
        $limit  = isset($params['limit']) ? (int)$params['limit'] : 100;

        $export = isset($params['export']) && filter_var($params['export'], FILTER_VALIDATE_BOOLEAN);
        $type   = isset($params['type']) ? strtolower(trim((string)$params['type'])) : null;

        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $messages = $this->outMessageRepo->getAll(
            $q,
            $sortKey,
            $sortOrder,
            $offset,
            $limit,
            $startDate,
            $endDate,
            $channel,
            $clientId
        );

        // Export
        if ($export && $type === 'csv') {

            $columns = [
                'id'         => 'Message ID',
                'api_client' => 'Client Name',
                'channel'    => 'Channel',
                'recipient'  => 'Recipient',
                'subject'    => 'Subject',
                'status'     => 'Status',
                'created_at' => 'Sent At',
            ];

            $exporter = new CsvExporter();
            $csv = $exporter->generate($messages, $columns);

            $filename = 'outgoing-messages-' . date('Y-m-d') . '.csv';

            $response = $this->response
                ->withHeader('Content-Type', 'text/csv')
                ->withHeader('Content-Disposition', "attachment; filename=\"$filename\"");

            $response->getBody()->write($csv);

            return $response;
        }

        return $this->respondWithData($messages, 200);
    }
}
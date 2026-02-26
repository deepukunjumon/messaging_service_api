<?php

declare(strict_types=1);

namespace App\Application\Actions\OutgoingMessage;

use Psr\Http\Message\ResponseInterface as Response;

final class GetAllOutgoingMessagesAction extends OutgoingMessageAction
{
    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $params = $this->request->getQueryParams();

        $q = isset($params['q']) ? trim((string)$params['q']) : '';
        $startDate = isset($params['startDate']) ? trim((string)$params['startDate']) : null;
        $endDate = isset($params['endDate']) ? trim((string)$params['endDate']) : null;
        $channel = isset($params['channel']) ? trim((string)$params['channel']) : null;
        $clientId = isset($params['client_id']) ? trim((string)$params['client_id']) : null;
        $sortKey = isset($params['sort_key']) ? trim((string)$params['sort_key']) : 'created_at';
        $sortOrder = isset($params['sort_order']) ? trim((string)$params['sort_order']) : 'desc';
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 100;

        $messages = $this->outMessageRepo->getAll($q, $sortKey, $sortOrder, $offset, $limit, $startDate, $endDate, $channel, $clientId);

        return $this->respondWithData($messages, 200);
    }
}
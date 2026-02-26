<?php

declare(strict_types=1);

namespace App\Application\Actions\OutgoingMessage;

use Psr\Http\Message\ResponseInterface as Response;

final class GetAllOutgoingMessagesAction extends OutgoingMessageAction
{
    protected function action(): Response
    {
        $params = $this->request->getQueryParams();

        $from     = trim((string)($params['from'] ?? ''));
        $to       = trim((string)($params['to'] ?? ''));
        $channel  = trim((string)($params['channel'] ?? ''));
        $clientId = trim((string)($params['client_id'] ?? ''));

        $messages = $this->outMessageRepo->getAll($from, $to, $channel, $clientId);

        return $this->respondWithData($messages);
    }
}


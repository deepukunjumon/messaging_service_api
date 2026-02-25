<?php

declare(strict_types=1);

namespace App\Infrastructure\Response;

use Psr\Http\Message\ResponseInterface;

final class ResponseEmitter
{
    /**
     * Emits a PSR-7 response to the client.
     */
    public function emit(ResponseInterface $response): void
    {
        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true, $response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            echo $body->read(8192);
            if (connection_status() !== CONNECTION_NORMAL) {
                break;
            }
        }
    }
}
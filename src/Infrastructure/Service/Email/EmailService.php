<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Email;

use App\Domain\Messaging\EmailMessage;
use App\Domain\Service\EmailServiceInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Throwable;

final class EmailService implements EmailServiceInterface
{
    private Client $client;
    private array $config;
    private LoggerInterface $logger;

    public function __construct(
        array $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->client = new Client([
            'base_uri' => rtrim($this->config['api_url'], '/') . '/',
            'timeout'  => 15.0,
            'verify'   => false
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function send(EmailMessage $message): array
    {
        try {

            $payload = [
                'from' => [
                    'email' => $this->config['from_email'],
                    'name'  => $this->config['from_name'],
                ],
                'subject' => $message->subject,
                'content' => [
                    [
                        'type'  => $message->isHtml ? 'html' : 'text',
                        'value' => $message->body,
                    ]
                ],
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => $message->to]
                        ]
                    ]
                ]
            ];

            $response = $this->client->post('mail/send', [
                'headers' => [
                    'api_key'      => $this->config['api_key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $status = $response->getStatusCode();
            $body   = (string) $response->getBody();

            $this->logger->info('Netcore Email Response', ['status' => $status, 'body' => $body, 'recipient' => $message->to]);

            if ($status === 200) {
                return ['success' => true, 'message' => 'Sent successfully', 'provider_response' => json_encode($body)];
            }
            return ['success' => false, 'message' => 'Sending Email failed', 'provider_response' => json_encode($body)];

        } catch (Throwable $e) {

            $this->logger->error('Email sending failed', ['error' => $e->getMessage(), 'recipient' => $message->to]);

            return ['success' => false, 'message' => 'Something went wrong', 'exception' => $e->getMessage()];
        }
    }
}
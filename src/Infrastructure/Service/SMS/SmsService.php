<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Sms;

use App\Domain\Messaging\SmsMessage;
use App\Service\SmsServiceInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Throwable;

final class SmsService implements SmsServiceInterface
{
    private Client $client;

    public function __construct(
        private readonly array $config,
        private readonly LoggerInterface $logger
    ) {
        $this->client = new Client([
            'base_uri' => $this->config['api_base_url'],
            'timeout'  => 15.0,
        ]);
    }

    public function sendSms(SmsMessage $message): array
    {
        try {

            $mobiles = implode(',', $message->getPhoneNumbers());

            $response = $this->client->get('sendhttp.php', [
                'query' => [
                    'authkey'   => $this->config['authkey'],
                    'mobiles'   => $mobiles,
                    'message'   => $message->getMessage(),
                    'sender'    => $this->config['sender'],
                    'route'     => $this->config['route'],
                    'country'   => $this->config['country'],
                    'DLT_TE_ID' => $message->getMeta('dlt_template_id'),
                ]
            ]);

            $statusCode   = $response->getStatusCode();
            $responseBody = (string) $response->getBody();

            $isSuccess = $statusCode === 200;

            return [
                'success'           => $isSuccess,
                'provider_response' => $responseBody,
            ];

        } catch (Throwable $e) {

            $this->logger->error('SMS sending failed', [
                'error'     => $e->getMessage(),
                'recipients'=> $message->getPhoneNumbers(),
            ]);

            return [
                'success' => false,
                'provider_response' => $e->getMessage(),
            ];
        }
    }
}
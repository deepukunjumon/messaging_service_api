<?php

declare(strict_types=1);

namespace App\Application\Actions\Email;

use App\Domain\Messaging\EmailMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Throwable;
use App\Infrastructure\Validation\ValidationUtil;

final class SendEmailAction extends EmailAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        $client = $this->client();
        $data   = $this->getFormData();

        // Sanitize Input
        $input = [
            'to'      => trim((string)($data['to'] ?? '')),
            'subject' => trim((string)($data['subject'] ?? '')),
            'body'    => trim((string)($data['body'] ?? '')),
        ];

        $isHtml = (bool)($data['isHtml'] ?? true);

        // Validation
        $rules = [
            'to' => [
                'rule' => v::notEmpty()->email(),
                'message' => 'Valid email is required'
            ],
            'subject' => [
                'rule' => v::stringType()->length(3, 255),
                'message' => 'Subject must be between 3 and 255 characters'
            ],
            'body' => [
                'rule' => v::stringType()->length(5, null),
                'message' => 'Body must be at least 5 characters'
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
            
            $result = $this->database->transaction(function () use ($client, $input, $isHtml) {

                $messageId = $this->outMessageRepo->create(
                    $client['id'],
                    $client['api_key_id'] ?? null,
                    'email',
                    $input['to'],
                    $input['subject'],
                    $input['body'],
                    'netcore'
                );

                $emailMessage = new EmailMessage(
                    $input['to'],
                    $input['subject'],
                    $input['body'],
                    $isHtml
                );

                $providerResult = $this->emailService->send($emailMessage);

                if ($providerResult['success'] ?? false) {
                    $this->outMessageRepo->markSent($messageId);
                } else {
                    $this->outMessageRepo->markFailed(
                        $messageId,
                        $providerResult['provider_response'] ?? 'Unknown provider error'
                    );
                }

                return [
                    'message_id' => $messageId,
                    'provider'   => $providerResult
                ];
            });

            if (!($result['provider']['success'] ?? false)) {
                return $this->respondWithError(
                    'Provider failed to send email',
                    502,
                    [
                        'message_id' => $result['message_id']
                    ]
                );
            }

            return $this->respondWithData(
                [
                    'message_id' => $result['message_id']
                ],
                201
            );

        } catch (Throwable $e) {

            $this->logger->error('Email transaction failed', [$e->getMessage()]);

            return $this->respondWithError(
                'Internal processing error',
                500
            );
        }
    }
}
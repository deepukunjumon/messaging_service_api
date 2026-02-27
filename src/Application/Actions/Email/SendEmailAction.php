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

        $cleanEmails = function($input) {
        if (is_array($input)) {
            return array_filter(array_map('trim', $input));
        }
            return array_filter(array_map('trim', explode(',', (string)($input ?? ''))));
        };

        $toEmails  = $cleanEmails($data['to'] ?? []);
        $ccEmails  = $cleanEmails($data['cc'] ?? []);
        $bccEmails = $cleanEmails($data['bcc'] ?? []);

        $input = [
            'to'          => implode(',', $toEmails),
            'cc'          => implode(',', $ccEmails),
            'bcc'         => implode(',', $bccEmails),
            'subject'     => trim((string)($data['subject'] ?? '')),
            'body'        => trim((string)($data['body'] ?? '')),
            'attachments' => $data['attachments'] ?? []
        ];

        $isHtml = (bool)($data['isHtml'] ?? true);

        $rules = [
            'to' => [
                'rule' => v::notEmpty(),
                'message' => 'At least one recipient is required'
            ],
            'subject' => [
                'rule' => v::stringType()->length(3, 255),
                'message' => 'Subject must be between 3 and 255 characters'
            ],
            'body' => [
                'rule' => v::stringType()->length(5, null),
                'message' => 'Body must be at least 5 characters'
            ],
            'attachments' => [
                'rule' => v::arrayType(),
                'message' => 'Attachments must be an array'
            ]
        ];

        $errors = ValidationUtil::validate($input, $rules);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed', 422, $errors);
        }

        try {
            $result = $this->database->transaction(function () use ($client, $input, $isHtml, $toEmails, $ccEmails, $bccEmails) {

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
                    $toEmails,
                    $input['subject'],
                    $input['body'],
                    $ccEmails,
                    $bccEmails,
                    $input['attachments'],
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
                    ['message_id' => $result['message_id'], 'details' => $result['provider']['message']]
                );
            }

            return $this->respondWithData(['message_id' => $result['message_id']], 201);

        } catch (Throwable $e) {
            $this->logger->error('Email transaction failed', [$e->getMessage()]);
            return $this->respondWithError('Internal processing error', 500);
        }
    }
}
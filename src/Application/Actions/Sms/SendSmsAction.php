<?php

declare(strict_types=1);

namespace App\Application\Actions\Sms;

use App\Domain\Messaging\SmsMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator;
use Slim\Exception\HttpBadRequestException;
use Throwable;

final class SendSmsAction extends SmsAction
{
    protected function action(): Response
    {
        $client = $this->client();

        if (!$client) {
            throw new HttpBadRequestException(
                $this->request,
                'Unauthorized client'
            );
        }

        $requestId = $this->requestId();
        $data = $this->getFormData();
        $phoneNumbers = $data['phoneNumbers'] ?? [];

        if (!is_array($phoneNumbers)) {
            $phoneNumbers = [$phoneNumbers];
        }

        $phoneNumbers = array_map(
            fn($n) => trim((string)$n),
            $phoneNumbers
        );

        // Remove duplicates
        $phoneNumbers = array_values(array_unique($phoneNumbers));

        $content = trim((string)($data['content'] ?? ''));
        $dltTemplateId = trim((string)($data['dlt_template_id'] ?? ''));

        // Validations
        $errors = [];

        if (empty($phoneNumbers)) {
            $errors['phoneNumbers'][] = 'At least one phone number is required';
        } else {
            foreach ($phoneNumbers as $number) {
                if (!Validator::regex('/^[6-9]\d{9}$/')->validate($number)) {
                    $errors['phoneNumbers'][] = "Invalid mobile number: {$number}";
                }
            }
        }

        if (!Validator::notEmpty()->validate($content)) {
            $errors['content'] = 'Message content cannot be blank';
        }

        if (!Validator::notEmpty()->validate($dltTemplateId)) {
            $errors['dlt_template_id'] = 'DLT Template ID is required';
        }

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed', 422, $errors);
        }

        $messageIds = [];
        $failedRecipients = [];
        $providerResponse = null;

        try {

            foreach ($phoneNumbers as $number) {

                $messageIds[$number] = $this->outMessageRepo->create(
                    $client['id'],
                    $client['api_key_id'] ?? null,
                    'sms',
                    $number,
                    null,
                    $content,
                    'moplet',
                    [
                        'request_id'      => $requestId,
                        'dlt_template_id' => $dltTemplateId
                    ]
                );
            }

            $smsMessage = new SmsMessage(
                $phoneNumbers,
                $content,
                [
                    'dlt_template_id' => $dltTemplateId
                ]
            );

            $result = $this->smsService->send($smsMessage);

            $isSuccess = (bool)($result['success'] ?? false);
            $providerResponse = $result['provider_response'] ?? null;

            foreach ($messageIds as $number => $id) {

                if ($isSuccess) {
                    $this->outMessageRepo->markSent(
                        $id,
                        $providerResponse
                    );
                } else {
                    $this->outMessageRepo->markFailed(
                        $id,
                        $providerResponse ?? 'Provider rejected SMS'
                    );

                    $failedRecipients[] = $number;
                }
            }

            if (!$isSuccess) {
                return $this->respondWithError(
                    'Provider failed to send SMS',
                    502,
                    [
                        'failed_recipients' => $failedRecipients
                    ]
                );
            }

            return $this->respondWithData(
                [
                    'total_recipients' => count($phoneNumbers),
                    'message_ids'      => array_values($messageIds)
                ],
                200
            );

        } catch (Throwable $e) {

            $contextType = count($phoneNumbers) > 1 ? 'Bulk SMS' : 'SMS';

            $this->logger->error("{$contextType} processing failed", [
                'error'            => $e->getMessage(),
                'recipients'       => $phoneNumbers,
                'recipients_count' => count($phoneNumbers),
                'request_id'       => $requestId
            ]);

            foreach ($messageIds as $id) {
                $this->outMessageRepo->markFailed(
                    $id,
                    $e->getMessage()
                );
            }

            return $this->respondWithError(
                'Internal processing error',
                500
            );
        }
    }
}
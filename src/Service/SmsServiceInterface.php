<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Messaging\SmsMessage;

interface SmsServiceInterface
{
    /**
     * Undocumented function
     *
     * @param SmsMessage $message
     * @return array
     */
    public function sendSms(SmsMessage $message): array;
}
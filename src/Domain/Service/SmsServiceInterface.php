<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Messaging\SmsMessage;

interface SmsServiceInterface
{
    /**
     * Send a message. Preferred method name.
     *
     * @param SmsMessage $message
     */
    public function send(SmsMessage $message): array;

}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Messaging\EmailMessage;

interface EmailServiceInterface
{
    /**
     * Send an email.
     *
     * @param EmailMessage $message
     * @return array
     */
    public function send(EmailMessage $message): array;
}
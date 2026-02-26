<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Messaging\EmailMessage;

interface EmailServiceInterface
{
    public function send(EmailMessage $message): array;
}

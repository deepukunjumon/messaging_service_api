<?php

declare(strict_types=1);

namespace App\Domain\Messaging;

final class EmailMessage
{
    public string $to;
    public string $subject;
    public string $body;
    public bool $isHtml;

    public function __construct(
        string $to,
        string $subject,
        string $body,
        bool $isHtml = true
    ) {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->isHtml = $isHtml;
    }
}
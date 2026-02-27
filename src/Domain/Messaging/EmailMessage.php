<?php

declare(strict_types=1);

namespace App\Domain\Messaging;

final class EmailMessage
{
    public array $to;
    public array $cc;
    public array $bcc;
    public string $subject;
    public string $body;
    public array $attachments;
    public bool $isHtml;

    public function __construct(
        array $to,
        string $subject,
        string $body,
        array $cc = [],
        array $bcc = [],
        array $attachments = [],
        bool $isHtml = true
    ) {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->attachments = $attachments;
        $this->isHtml = $isHtml;
    }
}
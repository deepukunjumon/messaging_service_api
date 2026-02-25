<?php

declare(strict_types=1);

namespace App\Domain\Messaging;

final class SmsMessage
{
    private array $phoneNumbers;
    private string $message;
    private array $meta;

    public function __construct(
        string|array $phoneNumbers,
        string $message,
        array $meta = []
    ) {
        $this->phoneNumbers = is_array($phoneNumbers)
            ? $phoneNumbers
            : [$phoneNumbers];

        $this->message = $message;
        $this->meta = $meta;
    }

    public function getPhoneNumbers(): array
    {
        return $this->phoneNumbers;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMeta(string $key): mixed
    {
        return $this->meta[$key] ?? null;
    }
}
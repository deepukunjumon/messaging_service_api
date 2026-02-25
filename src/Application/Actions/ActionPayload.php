<?php

declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;

final class ActionPayload implements JsonSerializable
{
    private int $statusCode;
    private bool $success;
    private mixed $data;
    private mixed $error;

    public function __construct(
        int $statusCode = 200,
        mixed $data = null,
        mixed $error = null
    ) {
        $this->statusCode = $statusCode;
        $this->success    = $statusCode < 400;
        $this->data       = $data;
        $this->error      = $error;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getError(): mixed
    {
        return $this->error;
    }

    public function jsonSerialize(): array
    {
        $response = [
            'status'  => $this->statusCode,
            'success' => $this->success,
        ];

        if ($this->success) {
            $response['data'] = $this->data;
        } else {
            $response['error'] = $this->error;
        }

        return $response;
    }
}
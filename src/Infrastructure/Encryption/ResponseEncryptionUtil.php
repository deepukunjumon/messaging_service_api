<?php

declare(strict_types=1);

namespace App\Infrastructure\Encryption;

final class ResponseEncryptionUtil
{
    private const CIPHER = 'AES-256-CBC';
    private const IV_LENGTH = 16;

    /**
     * Encrypts a JSON string and returns base64(iv + ciphertext).
     */
    public static function encrypt(string $plaintextJson, string $key): string
    {
        $key = substr(hash('sha256', $key, true), 0, 32);
        $iv  = random_bytes(self::IV_LENGTH);

        $ciphertext = openssl_encrypt(
            $plaintextJson,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Failed to encrypt response');
        }

        // Frontend will base64-decode and split iv + ciphertext
        return base64_encode($iv . $ciphertext);
    }
}
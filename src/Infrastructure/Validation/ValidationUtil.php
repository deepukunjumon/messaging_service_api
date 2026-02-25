<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

use Respect\Validation\Validator as v;

final class ValidationUtil
{
    /**
     * Validate input against rule map
     *
     * @param array $input
     * @param array $rules
     * @return array Errors
     */
    public static function validate(array $input, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $config) {

            $value = $input[$field] ?? null;

            if (!$config['rule']->validate($value)) {
                $errors[$field] = $config['message'];
            }
        }

        return $errors;
    }
}
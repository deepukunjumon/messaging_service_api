<?php

declare(strict_types=1);

/**
 * Mail Configuration
 */
return function (string $mode): array {

    if ($mode === 'PROD') {
        return [
            'api_key'    => 'ffc91538275fd5d407770d479cd9da82',
            'from_email' => 'care@acumengroup.in',
            'from_name'  => 'Acumen Messaging Service',
            'api_url'    => 'https://emailapi.netcorecloud.net/v5/',
        ];
    }

    if ($mode === 'DEV') {
        return [
            'api_key'    => 'ffc91538275fd5d407770d479cd9da82',
            'from_email' => 'care@acumengroup.in',
            'from_name'  => 'Acumen Messaging Service - Dev',
            'api_url'    => 'https://emailapi.netcorecloud.net/v5/',
        ];
    }

    throw new RuntimeException("Invalid APP mode: {$mode}");
};
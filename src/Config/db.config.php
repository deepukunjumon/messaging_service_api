<?php

declare(strict_types=1);

return function (string $mode): array {

    if ($mode === 'PROD') {
        return [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            'port'     => 3306,
            'dbname'   => 'messaging_service_db',
            'username' => '',
            'password' => '',
            'charset'  => 'utf8mb4',
        ];
    }

    if ($mode === 'DEV') {
        return [
            'driver'   => 'mysql',
            'host'     => '172.25.9.90',
            'port'     => 3306,
            'dbname'   => 'messaging_service_db',
            'username' => 'aceuser',
            'password' => 'shylesh@123',
            'charset'  => 'utf8mb4',
        ];
    }

    throw new RuntimeException("Invalid APP mode: {$mode}");
};
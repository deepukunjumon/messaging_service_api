<?php

declare(strict_types=1);

$appConfig  = require __DIR__ . '/app.config.php';
$dbConfig   = require __DIR__ . '/db.config.php';
$mailConfig = require __DIR__ . '/mail.config.php';
$smsConfig = require __DIR__ . '/sms.config.php';

return [
    'app'  => $appConfig,
    'db'   => $dbConfig($appConfig['mode']),
    'mail' => $mailConfig($appConfig['mode']),
    'sms'  => $smsConfig($appConfig['mode'])
];
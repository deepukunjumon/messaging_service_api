<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Service\EmailServiceInterface;
use App\Infrastructure\Service\Email\EmailService;
use App\Service\SmsServiceInterface;
use App\Infrastructure\Service\Sms\SmsService;
use App\Infrastructure\Database\Database;

$builder = new ContainerBuilder();

$builder->addDefinitions([

    Database::class => function ($c) {
        $config = $c->get('config')['db'];
        return new Database($config);
    },

    \PDO::class => function ($c) {
        return $c->get(Database::class)->getConnection();
    },

    Logger::class => function () {
        $logger = new Logger('messaging');
        $logger->pushHandler(
            new StreamHandler(__DIR__ . '/logs/app.log', Logger::DEBUG)
        );
        return $logger;
    },

    \Psr\Log\LoggerInterface::class => DI\get(Logger::class),

    'config' => function () {
        return require __DIR__ . '/Config/config.php';
    },

    EmailServiceInterface::class => function ($c) {
        $config = $c->get('config')['mail'];

        return new EmailService(
            $config,
            $c->get(\Psr\Log\LoggerInterface::class)
        );
    },

    SmsServiceInterface::class => function ($c) {
        $config = $c->get('config')['sms'];

        return new SmsService(
            $config,
            $c->get(\Psr\Log\LoggerInterface::class)
        );
    },
]);

return $builder->build();
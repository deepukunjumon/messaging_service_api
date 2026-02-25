<?php

declare(strict_types=1);

namespace App\Application\Actions\Sms;

use App\Application\Actions\Action;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Repository\OutgoingMessageRepository;
use App\Service\SmsServiceInterface;
use Psr\Log\LoggerInterface;

abstract class SmsAction extends Action
{
    protected SmsServiceInterface $smsService;
    protected OutgoingMessageRepository $outMessageRepo;
    protected Database $database;

    public function __construct(LoggerInterface $logger, SmsServiceInterface $smsService, OutgoingMessageRepository $outMessageRepo, Database $database)
    {
        parent::__construct($logger);
        $this->smsService = $smsService;
        $this->outMessageRepo = $outMessageRepo;
        $this->database = $database;
    }
}
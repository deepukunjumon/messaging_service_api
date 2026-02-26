<?php

declare(strict_types=1);

namespace App\Application\Actions\Email;

use App\Application\Actions\Action;
use App\Infrastructure\Database\Database;
use App\Domain\OutgoingMessage\OutgoingMessageRepositoryInterface;
use App\Domain\Service\EmailServiceInterface;
use Psr\Log\LoggerInterface;

abstract class EmailAction extends Action
{
    protected EmailServiceInterface $emailService;
    protected OutgoingMessageRepositoryInterface $outMessageRepo;
    protected Database $database;

    public function __construct(LoggerInterface $logger, EmailServiceInterface $emailService, OutgoingMessageRepositoryInterface $outMessageRepo, Database $database)
    {
        parent::__construct($logger);
        $this->emailService = $emailService;
        $this->outMessageRepo = $outMessageRepo;
        $this->database = $database;
    }
}
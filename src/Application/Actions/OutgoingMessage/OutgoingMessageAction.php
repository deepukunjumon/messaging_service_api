<?php

declare(strict_types=1);

namespace App\Application\Actions\OutgoingMessage;

use App\Application\Actions\Action;
use App\Infrastructure\Repository\OutgoingMessageRepository;
use Psr\Log\LoggerInterface;

abstract class OutgoingMessageAction extends Action
{
    protected OutgoingMessageRepository $outMessageRepo;

    public function __construct(LoggerInterface $logger, OutgoingMessageRepository $outMessageRepo)
    {
        parent::__construct($logger);
        $this->outMessageRepo = $outMessageRepo;
    }
}
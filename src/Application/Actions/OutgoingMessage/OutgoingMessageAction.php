<?php

declare(strict_types=1);

namespace App\Application\Actions\OutgoingMessage;

use App\Application\Actions\Action;
use App\Domain\OutgoingMessage\OutgoingMessageRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class OutgoingMessageAction extends Action
{
    protected OutgoingMessageRepositoryInterface $outMessageRepo;

    public function __construct(LoggerInterface $logger, OutgoingMessageRepositoryInterface $outMessageRepo)
    {
        parent::__construct($logger);
        $this->outMessageRepo = $outMessageRepo;
    }
}
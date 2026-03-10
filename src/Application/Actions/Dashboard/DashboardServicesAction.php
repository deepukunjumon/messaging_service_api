<?php

declare(strict_types=1);

namespace App\Application\Actions\Dashboard;

use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

final class DashboardServicesAction extends DashboardAction
{
    /**
     * {@inheritDoc}
     */
    protected function action(): Response
    {
        try {
            $services = [
                [
                    'service' => 'sms',
                    'label'   => 'SMS Service',
                    'status'  => 1
                ],
                [
                    'service' => 'email',
                    'label'   => 'Email Service',
                    'status'  => 1
                ],
                [
                    'service' => 'whatsapp',
                    'label'   => 'WhatsApp Service',
                    'status'  => -1
                ],
            ];

            return $this->respondWithData($services, 200);
        }
        catch (Throwable $e) {
            $this->logger->error('Failed to fetch services', ['error' => $e->getMessage()]);

            return $this->respondWithError(
                'An error occurred while services',
                500
            );
        }
    }
}
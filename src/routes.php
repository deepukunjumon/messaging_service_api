<?php

use Slim\App;
use App\Application\Actions\Email\SendEmailAction;
use App\Application\Actions\SMS\SendSmsAction;
use App\Application\Actions\ApiClient\CreateClientAction;
use App\Application\Actions\ApiClient\GenerateApiKeyAction;
use App\Application\Middleware\ApiKeyMiddleware;

return function (App $app) {

    $app->add(function ($request, $handler) {

        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            return $response
                ->withHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-KEY')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        }

        $response = $handler->handle($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-KEY')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    });

    $app->get('/health', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'status' => 'ok',
            'service' => 'messaging'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('/api/v1', function ($group) {

        // Client Management
        $group->post('/client', CreateClientAction::class);
        $group->post('/clients/{id}/keys', GenerateApiKeyAction::class);

        // Email Routes
        $group->group('/email', function ($group) {
            $group->post('/send', SendEmailAction::class);
        })->add(ApiKeyMiddleware::class);

        // SMS Routes
        $group->group('/sms', function ($group) {
            $group->post('/send', SendSmsAction::class);
        })->add(ApiKeyMiddleware::class);

    });

};
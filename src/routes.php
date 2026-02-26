<?php

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Actions\Email\SendEmailAction;
use App\Application\Actions\SMS\SendSmsAction;
use App\Application\Actions\ApiClient\CreateClientAction;
use App\Application\Actions\ApiClient\GenerateApiKeyAction;
use App\Application\Middleware\ApiKeyMiddleware;
use App\Application\Middleware\CorsMiddleware;

return function (App $app) {

    $app->add(new CorsMiddleware());

    $app->get('/health', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'status' => 'ok',
            'service' => 'messaging'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('/api/v1', function ($group) {

        // Client Management
        $group->post('/client', CreateClientAction::class);
        $group->post('/clients/{clientId}/keys', GenerateApiKeyAction::class);

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
<?php

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Actions\Email\SendEmailAction;
use App\Application\Actions\SMS\SendSmsAction;
use App\Application\Actions\ApiClient\CreateClientAction;
use App\Application\Actions\ApiClient\UpdateClientAction;
use App\Application\Actions\ApiClient\UpdateClientStatusAction;
use App\Application\Actions\ApiClient\GenerateApiKeyAction;
use App\Application\Actions\ApiClient\UpdateApiKeyStatusAction;
use App\Application\Actions\ApiClient\GetAllApiClientsAction;
use App\Application\Actions\ApiClient\GetClientApiKeysAction;
use App\Application\Actions\OutgoingMessage\GetAllOutgoingMessagesAction;
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
        $group->post('/api-client', CreateClientAction::class);
        $group->put('/api-clients/{clientId}/status', UpdateClientStatusAction::Class);
        $group->put('/api-clients/{clientId}', UpdateClientAction::Class);
        $group->get('/api-clients/{clientId}/keys', GetClientApiKeysAction::class);
        $group->get('/api-clients', GetAllApiClientsAction::class);
        
        // API Key Routes
        $group->post('/api-keys/generate/{clientId}', GenerateApiKeyAction::class);
        $group->put('/api-keys/{apiKeyId}/status', UpdateApiKeyStatusAction::class);

        // Email Routes
        $group->group('/email', function ($group) {
            $group->post('/send', SendEmailAction::class);
        })->add(ApiKeyMiddleware::class);

        // SMS Routes
        $group->group('/sms', function ($group) {
            $group->post('/send', SendSmsAction::class);
        })->add(ApiKeyMiddleware::class);

        // Outgoing Messages
        $group->get('/outgoing-messages', GetAllOutgoingMessagesAction::class);

    });

};
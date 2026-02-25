<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Infrastructure\Repository\ApiKeyRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class ApiKeyMiddleware
{
    private ApiKeyRepository $apiKeyRepository;

    public function __construct(ApiKeyRepository $apiKeyRepository)
    {
        $this->apiKeyRepository = $apiKeyRepository;
    }

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        $apiKey = $request->getHeaderLine('X-API-KEY');

        if (empty($apiKey)) {
            return $this->unauthorized('API key missing');
        }

        $client = $this->apiKeyRepository->validate($apiKey);

        if (!$client) {
            return $this->unauthorized('Invalid or inactive API key');
        }

        // Attach client info to request
        $request = $request->withAttribute('client', $client);

        return $handler->handle($request);
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = new Response();

        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
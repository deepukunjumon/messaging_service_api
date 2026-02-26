<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class CorsMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            $response = new SlimResponse();
            return $this->withCorsHeaders($response);
        }

        $response = $handler->handle($request);
        return $this->withCorsHeaders($response);
    }

    private function withCorsHeaders(Response $response): Response
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-KEY')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    }
}
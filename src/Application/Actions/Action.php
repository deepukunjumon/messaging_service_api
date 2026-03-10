<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Throwable;
use App\Infrastructure\Encryption\ResponseEncryptionUtil;

abstract class Action
{
    protected LoggerInterface $logger;
    protected Request $request;
    protected Response $response;
    protected array $args = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request  = $request;
        $this->response = $response;
        $this->args     = $args;

        try {
            return $this->action();

        } catch (DomainRecordNotFoundException $e) {
            throw new HttpNotFoundException($request, $e->getMessage());

        } catch (HttpBadRequestException | HttpNotFoundException $e) {
            throw $e;

        } catch (Throwable $e) {

            $this->logger->error('Unhandled Action Exception', [
                'action' => static::class,
                'error'  => $e->getMessage(),
                'file'   => $e->getFile(),
                'line'   => $e->getLine(),
                'trace'  => $e->getTraceAsString(),
            ]);

            return $this->respondWithError('Internal Server Error', 500);
        }
    }

    /**
     * Child classes must implement this
     *
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;
    
    protected function client(): ?array
    {
        $client = $this->request->getAttribute('client');

        return is_array($client) ? $client : null;
    }

    protected function requestId(): ?array
    {
        return $this->request->getAttribute('requestId');
    }

    /**
     * Always return parsed body as array
     */
    protected function getFormData(): array
    {
        $parsed = $this->request->getParsedBody();

        if (is_array($parsed)) {
            return $parsed;
        }

        if (is_object($parsed)) {
            return (array) $parsed;
        }

        return [];
    }

    /**
     * Get query parameter safely
     */
    protected function getQueryParam(string $name, mixed $default = null): mixed
    {
        $params = $this->request->getQueryParams();
        return $params[$name] ?? $default;
    }

    /**
     * Resolve route argument safely
     *
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name): mixed
    {
        if (!array_key_exists($name, $this->args)) {
            throw new HttpBadRequestException(
                $this->request,
                "Missing required route parameter: {$name}"
            );
        }

        return $this->args[$name];
    }

    /**
     * Standard success response
     */
    protected function respondWithData(
        mixed $data = null,
        int $statusCode = 200
    ): Response {
        return $this->respond(
            new ActionPayload(
                $statusCode,
                $data,
                null
            )
        );
    }

    /**
     * Standard error response
     */
    protected function respondWithError(
        string $message,
        int $statusCode = 400,
        mixed $details = null
    ): Response {
        return $this->respond(
            new ActionPayload(
                $statusCode,
                null,
                [
                    'message' => $message,
                    'details' => $details
                ]
            )
        );
    }

    /**
     * JSON response emitter
     */
    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $config = require __DIR__ . '/../../Config/config.php';
        $appCfg = $config['app'] ?? [];
        $encryptEnabled = $appCfg['encryption_enabled'] ?? false;
        $encryptionKey  = $appCfg['encryption_key']    ?? null;
        if ($encryptEnabled && $encryptionKey) {

            $encrypted = ResponseEncryptionUtil::encrypt($json, $encryptionKey);
            $json = json_encode(
                [
                    'encrypted' => true,
                    'payload'   => $encrypted,
                ],
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        }
        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
        $response->getBody()->write($json);
        return $response;
    }

    /**
     * Reusable CSV export response (Excel-friendly)
     *
     * @param array<array<string,mixed>> $rows
     */
    protected function respondWithCsv(array $data, string $filename)
    {
        $stream = fopen('php://temp', 'w+');
    
        if (!empty($data)) {
            // Write headers
            fputcsv($stream, array_keys($data[0]), ',', '"', '\\');
    
            // Write rows
            foreach ($data as $row) {
                fputcsv($stream, $row, ',', '"', '\\');
            }
        }
    
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
    
        $response = $this->response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', "attachment; filename=\"$filename\"");
    
        $response->getBody()->write($csv);
    
        return $response;
    }
}
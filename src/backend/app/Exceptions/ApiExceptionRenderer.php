<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Renders every exception thrown under /api as the same JSON shape:
 *
 *     { "error": { "status": 422, "code": "validation_failed", "message": "...", "details": { "field": ["..."] } } }
 *
 * "details" is only present for validation errors. Stack traces are never included; unexpected
 * errors are still reported to the log by Laravel's handler.
 */
class ApiExceptionRenderer
{
    private const CODES = [
        400 => 'bad_request',
        401 => 'unauthenticated',
        403 => 'forbidden',
        404 => 'not_found',
        405 => 'method_not_allowed',
        409 => 'conflict',
        413 => 'payload_too_large',
        415 => 'unsupported_media_type',
        419 => 'session_expired',
        422 => 'validation_failed',
        429 => 'too_many_requests',
        503 => 'service_unavailable',
    ];

    public function __invoke(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        if ($exception instanceof ValidationException) {
            return $this->response(422, $exception->getMessage(), $exception->errors());
        }

        if ($exception instanceof AuthenticationException) {
            return $this->response(401, 'You need to sign in to do that.');
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $this->response(
                $exception->getStatusCode(),
                $this->httpMessage($exception),
                headers: $exception->getHeaders(),
            );
        }

        return $this->response(500, 'Something went wrong on our side. Please try again.');
    }

    private function httpMessage(HttpExceptionInterface $exception): string
    {
        $previous = $exception instanceof Throwable ? $exception->getPrevious() : null;

        if ($previous instanceof ModelNotFoundException) {
            return class_basename($previous->getModel()).' not found.';
        }

        if ($exception->getStatusCode() === 404 && ! $exception->getMessage()) {
            return 'This API route does not exist.';
        }

        return $exception->getMessage() ?: (Response::$statusTexts[$exception->getStatusCode()] ?? 'Request failed.');
    }

    /**
     * @param  array<string, list<string>>|null  $details
     * @param  array<string, string>  $headers
     */
    private function response(int $status, string $message, ?array $details = null, array $headers = []): JsonResponse
    {
        $error = [
            'status' => $status,
            'code' => self::CODES[$status] ?? ($status >= 500 ? 'server_error' : 'http_error'),
            'message' => $message,
        ];

        if ($details !== null) {
            $error['details'] = $details;
        }

        return new JsonResponse(['error' => $error], $status, $headers);
    }
}

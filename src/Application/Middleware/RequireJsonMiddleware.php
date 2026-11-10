<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Actions\ActionError;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class RequireJsonMiddleware implements Middleware
{
    use JsonErrorResponse;

    public function process(Request $request, RequestHandler $handler): Response
    {
        $method = strtoupper($request->getMethod());
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $handler->handle($request);
        }

        $contentType = mb_strtolower(trim($request->getHeaderLine('Content-Type')));
        if (!str_starts_with($contentType, 'application/json')) {
            return $this->jsonError(415, ActionError::BAD_REQUEST, 'Content-Type harus application/json.');
        }

        return $handler->handle($request);
    }
}

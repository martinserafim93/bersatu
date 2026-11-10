<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

trait JsonErrorResponse
{
    private function jsonError(int $statusCode, string $type, string $description): Response
    {
        $payload = new ActionPayload($statusCode, null, new ActionError($type, $description));
        $response = new SlimResponse($statusCode);
        $response->getBody()->write((string) json_encode($payload, JSON_PRETTY_PRINT));

        return $response->withHeader('Content-Type', 'application/json');
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Actions\ActionError;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class RoleMiddleware implements Middleware
{
    use JsonErrorResponse;

    /**
     * @param array<int, string> $allowedRoles
     */
    public function __construct(private array $allowedRoles)
    {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        if (!($user instanceof User)) {
            return $this->jsonError(401, ActionError::UNAUTHENTICATED, 'Silakan masuk terlebih dahulu.');
        }

        if (!in_array($user->getRole(), $this->allowedRoles, true)) {
            return $this->jsonError(
                403,
                ActionError::INSUFFICIENT_PRIVILEGES,
                'Anda tidak memiliki akses ke sumber daya ini.'
            );
        }

        return $handler->handle($request);
    }
}

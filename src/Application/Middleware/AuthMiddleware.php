<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Actions\ActionError;
use App\Application\Auth\AuthSession;
use App\Domain\User\StatusAkun;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class AuthMiddleware implements Middleware
{
    use JsonErrorResponse;

    public function __construct(private UserRepository $users)
    {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $session = $request->getAttribute('session');
        if (!($session instanceof AuthSession) || $session->userId() === null) {
            return $this->jsonError(401, ActionError::UNAUTHENTICATED, 'Silakan masuk terlebih dahulu.');
        }

        try {
            $user = $this->users->findById($session->userId());
        } catch (UserNotFoundException) {
            $session->logout();
            return $this->jsonError(401, ActionError::UNAUTHENTICATED, 'Silakan masuk terlebih dahulu.');
        }

        if ($user->getStatusAkun() !== StatusAkun::AKTIF) {
            $session->logout();
            return $this->jsonError(401, ActionError::UNAUTHENTICATED, 'Sesi berakhir karena status akun berubah.');
        }

        return $handler->handle($request->withAttribute('user', $user));
    }
}

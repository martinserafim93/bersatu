<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Auth\AuthSession;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($request->getAttribute('session') instanceof AuthSession) {
            return $handler->handle($request);
        }

        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_name('BERSATUSESS');
                ini_set('session.use_strict_mode', '1');
                $isHttps = (($_SERVER['HTTPS'] ?? 'off') !== 'off');
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            @session_start();
        }

        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }

        $session = new AuthSession(
            $_SESSION,
            static function (): void {
                if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                    @session_regenerate_id(true);
                }
            },
            static function (): void {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    @session_destroy();
                }
            }
        );

        $request = $request->withAttribute('session', $session);
        $response = $handler->handle($request);

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }

        return $response;
    }
}

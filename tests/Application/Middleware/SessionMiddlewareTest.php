<?php

declare(strict_types=1);

namespace Tests\Application\Middleware;

use App\Application\Auth\AuthSession;
use App\Application\Middleware\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;
use Tests\TestCase;

class SessionMiddlewareTest extends TestCase
{
    public function testInjectedAuthSessionSkipsNativeSessionStart(): void
    {
        $middleware = new SessionMiddleware();
        $data = ['user_id' => 99];
        $session = new AuthSession($data, fn () => null, fn () => null);

        $request = $this->createRequest('GET', '/test')->withAttribute('session', $session);

        $captured = null;
        $handler = new class ($captured) implements RequestHandlerInterface {
            private ?ServerRequestInterface $cap;

            public function __construct(?ServerRequestInterface &$cap)
            {
                $this->cap = &$cap;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->cap = $request;
                return new SlimResponse(200);
            }
        };

        $response = $middleware->process($request, $handler);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($session, $captured->getAttribute('session'));
    }
}

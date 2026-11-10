<?php

declare(strict_types=1);

namespace Tests\Application\Middleware;

use App\Application\Middleware\RoleMiddleware;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    private function createHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new SlimResponse(200);
            }
        };
    }

    private function makeUser(string $role): User
    {
        return new User(
            1,
            'User',
            null,
            'user@example.com',
            'hash',
            $role,
            5,
            StatusAkun::AKTIF,
            'enc',
            'enc',
            '2026-11-09 10:00:00'
        );
    }

    public function testMissingUserAttributeReturns401(): void
    {
        $middleware = new RoleMiddleware([Role::SUPERADMIN]);
        $request = $this->createRequest('GET', '/admin');

        $response = $middleware->process($request, $this->createHandler());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAllowedRolePasses(): void
    {
        $middleware = new RoleMiddleware([Role::SUPERADMIN]);
        $request = $this->createRequest('GET', '/admin')
            ->withAttribute('user', $this->makeUser(Role::SUPERADMIN));

        $response = $middleware->process($request, $this->createHandler());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDisallowedRoleReturns403(): void
    {
        $middleware = new RoleMiddleware([Role::SUPERADMIN]);
        $disallowedRoles = [
            Role::PIMPINAN,
            Role::VERIFIKATOR,
            Role::PENYULUH_IPARI,
            Role::KUA,
            Role::FKUB,
            Role::MASYARAKAT,
        ];

        foreach ($disallowedRoles as $role) {
            $request = $this->createRequest('GET', '/admin')
                ->withAttribute('user', $this->makeUser($role));

            $response = $middleware->process($request, $this->createHandler());
            $this->assertSame(403, $response->getStatusCode(), "Role {$role} harus 403");
        }
    }

    public function testMultipleAllowedRoles(): void
    {
        $middleware = new RoleMiddleware([Role::PIMPINAN, Role::VERIFIKATOR]);

        $requestPimpinan = $this->createRequest('GET', '/ews')
            ->withAttribute('user', $this->makeUser(Role::PIMPINAN));
        $this->assertSame(200, $middleware->process($requestPimpinan, $this->createHandler())->getStatusCode());

        $requestVerifikator = $this->createRequest('GET', '/ews')
            ->withAttribute('user', $this->makeUser(Role::VERIFIKATOR));
        $this->assertSame(200, $middleware->process($requestVerifikator, $this->createHandler())->getStatusCode());

        $requestMasyarakat = $this->createRequest('GET', '/ews')
            ->withAttribute('user', $this->makeUser(Role::MASYARAKAT));
        $this->assertSame(403, $middleware->process($requestMasyarakat, $this->createHandler())->getStatusCode());
    }
}

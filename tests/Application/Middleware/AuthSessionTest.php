<?php

declare(strict_types=1);

namespace Tests\Application\Middleware;

use App\Application\Auth\AuthSession;
use Tests\TestCase;

class AuthSessionTest extends TestCase
{
    public function testLoginRegeneratesIdBeforeSettingUserId(): void
    {
        $data = [];
        $regenerated = false;
        $order = [];

        $session = new AuthSession(
            $data,
            function () use (&$regenerated, &$order, &$data): void {
                $regenerated = true;
                $order[] = 'regenerate';
                // Pada saat regenerate dipanggil, user_id belum diset
                $this->assertArrayNotHasKey('user_id', $data);
            },
            function (): void {
            }
        );

        $session->login(42);

        $this->assertTrue($regenerated);
        $this->assertSame(42, $session->userId());
        $this->assertSame(42, $data['user_id']);
    }

    public function testLogoutClearsDataAndDestroysSession(): void
    {
        $data = ['user_id' => 42, 'other' => 'value'];
        $destroyed = false;

        $session = new AuthSession(
            $data,
            function (): void {
            },
            function () use (&$destroyed): void {
                $destroyed = true;
            }
        );

        $session->logout();

        $this->assertTrue($destroyed);
        $this->assertNull($session->userId());
        $this->assertEmpty($data);
    }

    public function testUserIdReturnsNullWhenNotSetOrInvalid(): void
    {
        $data = [];
        $session = new AuthSession(
            $data,
            fn () => null,
            fn () => null
        );

        $this->assertNull($session->userId());

        $data['user_id'] = 'bukan-angka';
        $this->assertNull($session->userId());

        $data['user_id'] = '100';
        $this->assertSame(100, $session->userId());
    }
}

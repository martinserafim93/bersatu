<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    public function testRoleConstantsAndGroups(): void
    {
        $this->assertCount(4, Role::PUBLIC_REGISTRATION);
        $this->assertContains(Role::MASYARAKAT, Role::PUBLIC_REGISTRATION);
        $this->assertContains(Role::PENYULUH_IPARI, Role::PUBLIC_REGISTRATION);
        $this->assertContains(Role::KUA, Role::PUBLIC_REGISTRATION);
        $this->assertContains(Role::FKUB, Role::PUBLIC_REGISTRATION);

        $this->assertNotContains(Role::SUPERADMIN, Role::PUBLIC_REGISTRATION);
        $this->assertNotContains(Role::PIMPINAN, Role::PUBLIC_REGISTRATION);
        $this->assertNotContains(Role::VERIFIKATOR, Role::PUBLIC_REGISTRATION);

        $this->assertCount(7, Role::ALL);
        $this->assertSame(7, count(array_unique(Role::ALL)));

        $this->assertNotContains(Role::SUPERADMIN, Role::ADMIN_CREATABLE);
        $this->assertContains(Role::VERIFIKATOR, Role::ADMIN_CREATABLE);
        $this->assertContains(Role::PIMPINAN, Role::ADMIN_CREATABLE);
    }
}

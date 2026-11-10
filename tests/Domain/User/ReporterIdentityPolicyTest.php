<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\IdentityAccessDeniedException;
use App\Domain\User\ReporterIdentityPolicy;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use App\Infrastructure\Security\IdentityCipher;
use Tests\TestCase;

class ReporterIdentityPolicyTest extends TestCase
{
    private IdentityCipher $cipher;
    private ReporterIdentityPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cipher = new IdentityCipher(str_repeat('ab', 32));
        $this->policy = new ReporterIdentityPolicy($this->cipher);
    }

    private function makeUser(int $id, string $role, string $status = StatusAkun::AKTIF): User
    {
        return new User(
            $id,
            'User ' . $id,
            null,
            "user{$id}@example.com",
            'hash',
            $role,
            5,
            $status,
            $this->cipher->encrypt('650105123456000' . $id),
            $this->cipher->encrypt('62812345678' . $id),
            '2026-11-09 10:00:00'
        );
    }

    public function testCanViewRealIdentityAllowsVerifikatorAndTicketOwner(): void
    {
        $owner = $this->makeUser(1, Role::MASYARAKAT, StatusAkun::AKTIF);
        $verifikator = $this->makeUser(2, Role::VERIFIKATOR, StatusAkun::AKTIF);

        $this->assertTrue($this->policy->canViewRealIdentity($verifikator, $owner));
        $this->assertTrue($this->policy->canViewRealIdentity($owner, $owner));
    }

    public function testCanViewRealIdentityDeniesSuperadminPimpinanAndOthers(): void
    {
        $owner = $this->makeUser(1, Role::MASYARAKAT, StatusAkun::AKTIF);
        $superadmin = $this->makeUser(2, Role::SUPERADMIN, StatusAkun::AKTIF);
        $pimpinan = $this->makeUser(3, Role::PIMPINAN, StatusAkun::AKTIF);
        $otherMasyarakat = $this->makeUser(4, Role::MASYARAKAT, StatusAkun::AKTIF);
        $penyuluh = $this->makeUser(5, Role::PENYULUH_IPARI, StatusAkun::AKTIF);

        $this->assertFalse($this->policy->canViewRealIdentity($superadmin, $owner));
        $this->assertFalse($this->policy->canViewRealIdentity($pimpinan, $owner));
        $this->assertFalse($this->policy->canViewRealIdentity($otherMasyarakat, $owner));
        $this->assertFalse($this->policy->canViewRealIdentity($penyuluh, $owner));
    }

    public function testCanViewRealIdentityDeniesInactiveVerifikator(): void
    {
        $owner = $this->makeUser(1, Role::MASYARAKAT, StatusAkun::AKTIF);
        $inactiveVerifikator = $this->makeUser(2, Role::VERIFIKATOR, StatusAkun::NONAKTIF);

        $this->assertFalse($this->policy->canViewRealIdentity($inactiveVerifikator, $owner));
    }

    public function testRevealReturnsDecryptedValuesForAuthorizedViewer(): void
    {
        $owner = $this->makeUser(1, Role::MASYARAKAT, StatusAkun::AKTIF);
        $verifikator = $this->makeUser(2, Role::VERIFIKATOR, StatusAkun::AKTIF);

        $revealed = $this->policy->reveal($verifikator, $owner);

        $this->assertSame('6501051234560001', $revealed['nip_nik']);
        $this->assertSame('628123456781', $revealed['no_wa']);
    }

    public function testRevealThrowsIdentityAccessDeniedExceptionForUnauthorizedViewer(): void
    {
        $owner = $this->makeUser(1, Role::MASYARAKAT, StatusAkun::AKTIF);
        $superadmin = $this->makeUser(2, Role::SUPERADMIN, StatusAkun::AKTIF);

        $this->expectException(IdentityAccessDeniedException::class);
        $this->expectExceptionMessage('Anda tidak berhak melihat identitas ini.');
        $this->policy->reveal($superadmin, $owner);
    }
}

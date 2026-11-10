<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndJsonSerializeExcludesSensitiveFields(): void
    {
        $user = new User(
            1,
            'Ahmad Fauzi',
            'KUA Tanjung Selor',
            'ahmad@example.com',
            '$2y$10$hashedpasswordstring',
            Role::KUA,
            5,
            StatusAkun::AKTIF,
            'encrypted-nip-nik',
            'encrypted-no-wa',
            '2026-11-09 10:00:00'
        );

        $this->assertSame(1, $user->getId());
        $this->assertSame('Ahmad Fauzi', $user->getNamaLengkap());
        $this->assertSame('KUA Tanjung Selor', $user->getInstansi());
        $this->assertSame('ahmad@example.com', $user->getEmail());
        $this->assertSame('$2y$10$hashedpasswordstring', $user->getPasswordHash());
        $this->assertSame(Role::KUA, $user->getRole());
        $this->assertSame(5, $user->getWilayahId());
        $this->assertSame(StatusAkun::AKTIF, $user->getStatusAkun());
        $this->assertSame('encrypted-nip-nik', $user->getNipNikEncrypted());
        $this->assertSame('encrypted-no-wa', $user->getNoWaEncrypted());
        $this->assertSame('2026-11-09 10:00:00', $user->getCreatedAt());

        $serialized = $user->jsonSerialize();

        $expectedKeys = [
            'id',
            'nama_lengkap',
            'instansi',
            'email',
            'role',
            'wilayah_id',
            'status_akun',
            'created_at',
        ];
        $this->assertSame($expectedKeys, array_keys($serialized));

        $jsonString = (string) json_encode($serialized);
        $this->assertStringNotContainsString('password', strtolower($jsonString));
        $this->assertStringNotContainsString('hash', strtolower($jsonString));
        $this->assertStringNotContainsString('encrypted', strtolower($jsonString));
    }
}

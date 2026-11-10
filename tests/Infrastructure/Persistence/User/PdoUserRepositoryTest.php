<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\User;

use App\Domain\User\DuplicateUserException;
use App\Domain\User\NewUser;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\UserNotFoundException;
use App\Infrastructure\Persistence\User\PdoUserRepository;
use PDO;
use PDOException;
use PDOStatement;
use Tests\TestCase;

class PdoUserRepositoryTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function sampleUserRow(int $id = 1, string $status = StatusAkun::AKTIF): array
    {
        return [
            'id' => $id,
            'nip_nik_encrypted' => 'enc-nip-nik',
            'nama_lengkap' => 'Nama Pengguna',
            'instansi' => 'KUA Tanjung Selor',
            'no_wa_encrypted' => 'enc-no-wa',
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$dummyhash',
            'role' => Role::KUA,
            'wilayah_id' => 5,
            'status_akun' => $status,
            'created_at' => '2026-11-09 10:00:00',
        ];
    }

    public function testFindByIdReturnsMappedEntity(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['id' => 1])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn($this->sampleUserRow(1))->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted,'
            . ' email, password_hash, role, wilayah_id, status_akun, created_at FROM users WHERE id = :id LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $user = $repo->findById(1);
        $this->assertSame(1, $user->getId());
        $this->assertSame('Nama Pengguna', $user->getNamaLengkap());
        $this->assertSame('user@example.com', $user->getEmail());
        $this->assertSame(Role::KUA, $user->getRole());
    }

    public function testFindByIdThrowsExceptionWhenNotFound(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['id' => 999])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn(false)->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted,'
            . ' email, password_hash, role, wilayah_id, status_akun, created_at FROM users WHERE id = :id LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $this->expectException(UserNotFoundException::class);
        $repo->findById(999);
    }

    public function testFindByEmailReturnsUserWhenFound(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['email' => 'user@example.com'])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn($this->sampleUserRow(1))->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted,'
            . ' email, password_hash, role, wilayah_id, status_akun, created_at FROM users'
            . ' WHERE email = :email LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $user = $repo->findByEmail('user@example.com');
        $this->assertNotNull($user);
        $this->assertSame('user@example.com', $user->getEmail());
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['email' => 'missing@example.com'])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn(false)->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted,'
            . ' email, password_hash, role, wilayah_id, status_akun, created_at FROM users'
            . ' WHERE email = :email LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $this->assertNull($repo->findByEmail('missing@example.com'));
    }

    public function testFindByStatusWithoutFilter(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->fetch()->willReturn($this->sampleUserRow(1), $this->sampleUserRow(2), false);

        $pdo->query('SELECT id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted,'
            . ' email, password_hash, role, wilayah_id, status_akun, created_at FROM users'
            . ' ORDER BY created_at ASC, id ASC')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $users = $repo->findByStatus(null);
        $this->assertCount(2, $users);
    }

    public function testFindByStatusWithFilter(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['status' => StatusAkun::MENUNGGU_VERIFIKASI])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn($this->sampleUserRow(1, StatusAkun::MENUNGGU_VERIFIKASI), false);

        $pdo->prepare('SELECT id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted,'
            . ' email, password_hash, role, wilayah_id, status_akun, created_at FROM users'
            . ' WHERE status_akun = :status ORDER BY created_at ASC, id ASC')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $users = $repo->findByStatus(StatusAkun::MENUNGGU_VERIFIKASI);
        $this->assertCount(1, $users);
        $this->assertSame(StatusAkun::MENUNGGU_VERIFIKASI, $users[0]->getStatusAkun());
    }

    public function testCreateInsertsAndReturnsId(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $newUser = new NewUser(
            'enc-nip',
            'hash-nip',
            'Budi',
            null,
            'enc-wa',
            'budi@example.com',
            '$2y$10$hash',
            Role::MASYARAKAT,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI
        );

        $stmt->execute([
            'nip_nik_encrypted' => 'enc-nip',
            'nip_nik_hash' => 'hash-nip',
            'nama_lengkap' => 'Budi',
            'instansi' => null,
            'no_wa_encrypted' => 'enc-wa',
            'email' => 'budi@example.com',
            'password_hash' => '$2y$10$hash',
            'role' => Role::MASYARAKAT,
            'wilayah_id' => 5,
            'status_akun' => StatusAkun::MENUNGGU_VERIFIKASI,
        ])->shouldBeCalledOnce();

        $pdo->prepare(\Prophecy\Argument::containingString('INSERT INTO users'))
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        $pdo->lastInsertId()->willReturn('42')->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $id = $repo->create($newUser);
        $this->assertSame(42, $id);
    }

    public function testCreateDuplicateThrowsDuplicateUserException(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $newUser = new NewUser(
            'enc-nip',
            'hash-nip',
            'Budi',
            null,
            'enc-wa',
            'budi@example.com',
            '$2y$10$hash',
            Role::MASYARAKAT,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI
        );

        $pdoException = new PDOException('Duplicate entry');
        $pdoException->errorInfo = ['23000', 1062, 'Duplicate entry'];

        $stmt->execute(\Prophecy\Argument::any())->willThrow($pdoException);

        $pdo->prepare(\Prophecy\Argument::containingString('INSERT INTO users'))
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $this->expectException(DuplicateUserException::class);
        $repo->create($newUser);
    }

    public function testCreateOtherPdoExceptionIsRethrown(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $newUser = new NewUser(
            'enc-nip',
            'hash-nip',
            'Budi',
            null,
            'enc-wa',
            'budi@example.com',
            '$2y$10$hash',
            Role::MASYARAKAT,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI
        );

        $pdoException = new PDOException('Connection lost');
        $pdoException->errorInfo = ['HY000', 2006, 'MySQL server has gone away'];

        $stmt->execute(\Prophecy\Argument::any())->willThrow($pdoException);

        $pdo->prepare(\Prophecy\Argument::containingString('INSERT INTO users'))
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $this->expectException(PDOException::class);
        $repo->create($newUser);
    }

    public function testTransitionStatusUpdatesRowAndReturnsBoolean(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute([
            'to' => StatusAkun::AKTIF,
            'role' => Role::KUA,
            'id' => 10,
            'from' => StatusAkun::MENUNGGU_VERIFIKASI,
        ])->shouldBeCalledOnce();

        $stmt->rowCount()->willReturn(1)->shouldBeCalledOnce();

        $pdo->prepare('UPDATE users SET status_akun = :to, role = COALESCE(:role, role)'
            . ' WHERE id = :id AND status_akun = :from')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repo = new PdoUserRepository($pdoInstance);

        $this->assertTrue($repo->transitionStatus(10, StatusAkun::MENUNGGU_VERIFIKASI, StatusAkun::AKTIF, Role::KUA));
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\DuplicateUserException;
use App\Domain\User\NewUser;
use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;
use PDO;
use PDOException;

final class PdoUserRepository implements UserRepository
{
    private const COLUMNS = 'id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted,'
        . ' email, password_hash, role, wilayah_id, status_akun, created_at';

    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): User
    {
        $stmt = $this->pdo->prepare('SELECT ' . self::COLUMNS . ' FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();
        if ($row === false) {
            throw new UserNotFoundException();
        }

        return $this->mapRow($row);
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT ' . self::COLUMNS . ' FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return $this->mapRow($row);
    }

    public function findByStatus(?string $status): array
    {
        if ($status === null) {
            $stmt = $this->pdo->query('SELECT ' . self::COLUMNS . ' FROM users ORDER BY created_at ASC, id ASC');
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT ' . self::COLUMNS . ' FROM users WHERE status_akun = :status ORDER BY created_at ASC, id ASC'
            );
            $stmt->execute(['status' => $status]);
        }

        $users = [];
        while ($row = $stmt->fetch()) {
            /** @var array<string, mixed> $row */
            $users[] = $this->mapRow($row);
        }

        return $users;
    }

    public function create(NewUser $user): int
    {
        $sql = 'INSERT INTO users (
            nip_nik_encrypted, nip_nik_hash, nama_lengkap, instansi,
            no_wa_encrypted, email, password_hash, role, wilayah_id, status_akun
        ) VALUES (
            :nip_nik_encrypted, :nip_nik_hash, :nama_lengkap, :instansi,
            :no_wa_encrypted, :email, :password_hash, :role, :wilayah_id, :status_akun
        )';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'nip_nik_encrypted' => $user->nipNikEncrypted,
                'nip_nik_hash' => $user->nipNikHash,
                'nama_lengkap' => $user->namaLengkap,
                'instansi' => $user->instansi,
                'no_wa_encrypted' => $user->noWaEncrypted,
                'email' => $user->email,
                'password_hash' => $user->passwordHash,
                'role' => $user->role,
                'wilayah_id' => $user->wilayahId,
                'status_akun' => $user->statusAkun,
            ]);

            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if (($e->errorInfo[0] ?? '') === '23000') {
                throw new DuplicateUserException();
            }
            throw $e;
        }
    }

    public function transitionStatus(int $id, string $from, string $to, ?string $role = null): bool
    {
        $sql = 'UPDATE users SET status_akun = :to, role = COALESCE(:role, role)'
            . ' WHERE id = :id AND status_akun = :from';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'to' => $to,
            'role' => $role,
            'id' => $id,
            'from' => $from,
        ]);

        return $stmt->rowCount() === 1;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): User
    {
        return new User(
            (int) $row['id'],
            (string) $row['nama_lengkap'],
            $row['instansi'] !== null ? (string) $row['instansi'] : null,
            (string) $row['email'],
            (string) $row['password_hash'],
            (string) $row['role'],
            (int) $row['wilayah_id'],
            (string) $row['status_akun'],
            (string) $row['nip_nik_encrypted'],
            (string) $row['no_wa_encrypted'],
            (string) $row['created_at']
        );
    }
}

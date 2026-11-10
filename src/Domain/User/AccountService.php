<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Wilayah\WilayahNotFoundException;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Security\IdentityCipher;

class AccountService
{
    private static ?string $dummyHash = null;

    public function __construct(
        private UserRepository $users,
        private WilayahRepository $wilayah,
        private IdentityCipher $cipher
    ) {
    }

    private static function dummyHash(): string
    {
        if (self::$dummyHash === null) {
            self::$dummyHash = password_hash('dummy-password', PASSWORD_DEFAULT);
        }
        return self::$dummyHash;
    }

    /**
     * @param mixed $email
     * @param mixed $password
     * @return User
     * @throws AuthenticationFailedException
     * @throws AccountNotActiveException
     */
    public function authenticate(mixed $email, mixed $password): User
    {
        if (!is_string($email) || !is_string($password) || trim($email) === '' || $password === '') {
            throw new AuthenticationFailedException();
        }

        $cleanEmail = mb_strtolower(trim($email));
        $user = $this->users->findByEmail($cleanEmail);

        if ($user === null) {
            password_verify($password, self::dummyHash());
            throw new AuthenticationFailedException();
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            throw new AuthenticationFailedException();
        }

        if ($user->getStatusAkun() !== StatusAkun::AKTIF) {
            throw new AccountNotActiveException($user->getStatusAkun());
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $input
     * @return int
     * @throws ValidationException
     * @throws DuplicateUserException
     */
    public function register(array $input): int
    {
        return $this->createAccount(
            $input,
            $input['role'] ?? null,
            Role::PUBLIC_REGISTRATION,
            StatusAkun::MENUNGGU_VERIFIKASI
        );
    }

    /**
     * @param array<string, mixed> $input
     * @param mixed $role
     * @param array<int, string> $allowedRoles
     * @param string $status
     * @return int
     * @throws ValidationException
     * @throws DuplicateUserException
     */
    private function createAccount(array $input, mixed $role, array $allowedRoles, string $status): int
    {
        /** @var array<string, string> $errors */
        $errors = [];

        $roleStr = is_string($role) ? trim($role) : '';
        if (!in_array($roleStr, $allowedRoles, true)) {
            $errors['role'] = 'Peran tidak valid.';
        }

        $nama = isset($input['nama_lengkap']) && is_string($input['nama_lengkap'])
            ? trim($input['nama_lengkap'])
            : '';
        if ($nama === '' || mb_strlen($nama) > 150) {
            $errors['nama_lengkap'] = 'Nama lengkap wajib diisi (maksimal 150 karakter).';
        }

        $rawInstansi = isset($input['instansi']) && is_string($input['instansi'])
            ? trim($input['instansi'])
            : '';
        $finalInstansi = null;
        if (in_array($roleStr, Role::FIELD_REPORTERS, true)) {
            if ($rawInstansi === '' || mb_strlen($rawInstansi) > 150) {
                $errors['instansi'] = 'Instansi/lembaga wajib diisi (maksimal 150 karakter).';
            } else {
                $finalInstansi = $rawInstansi;
            }
        } elseif ($roleStr === Role::MASYARAKAT) {
            $finalInstansi = null;
        } else {
            if ($rawInstansi !== '') {
                if (mb_strlen($rawInstansi) > 150) {
                    $errors['instansi'] = 'Instansi/lembaga wajib diisi (maksimal 150 karakter).';
                } else {
                    $finalInstansi = $rawInstansi;
                }
            }
        }

        $rawNipNik = isset($input['nip_nik']) && is_string($input['nip_nik'])
            ? trim($input['nip_nik'])
            : '';
        if ($roleStr === Role::MASYARAKAT) {
            if (preg_match('/^\d{16}$/', $rawNipNik) !== 1) {
                $errors['nip_nik'] = 'NIK harus 16 digit angka.';
            }
        } else {
            if (preg_match('/^(\d{16}|\d{18})$/', $rawNipNik) !== 1) {
                $errors['nip_nik'] = 'NIP (18 digit) atau NIK (16 digit) harus berupa angka.';
            }
        }

        $rawWa = isset($input['no_wa']) && is_string($input['no_wa']) ? $input['no_wa'] : '';
        $waNormal = self::normalizeWa($rawWa);
        if ($waNormal === null) {
            $errors['no_wa'] = 'Nomor WhatsApp tidak valid.';
        }

        $rawEmail = isset($input['email']) && is_string($input['email'])
            ? mb_strtolower(trim($input['email']))
            : '';
        if ($rawEmail === '' || filter_var($rawEmail, FILTER_VALIDATE_EMAIL) === false || mb_strlen($rawEmail) > 100) {
            $errors['email'] = 'Email tidak valid.';
        }

        $rawPassword = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';
        if (mb_strlen($rawPassword) < 8 || strlen($rawPassword) > 72) {
            $errors['password'] = 'Kata sandi minimal 8 karakter dan maksimal 72 byte.';
        }

        $rawWilayahId = $input['wilayah_id'] ?? null;
        $isValidInt = false;
        $wilayahId = 0;
        if (is_int($rawWilayahId) && $rawWilayahId > 0) {
            $isValidInt = true;
            $wilayahId = $rawWilayahId;
        } elseif (is_string($rawWilayahId) && preg_match('/^[1-9]\d*$/', trim($rawWilayahId)) === 1) {
            $isValidInt = true;
            $wilayahId = (int) trim($rawWilayahId);
        }

        if (!$isValidInt) {
            $errors['wilayah_id'] = 'Wilayah wajib dipilih.';
        } else {
            try {
                $this->wilayah->findById($wilayahId);
            } catch (WilayahNotFoundException) {
                $errors['wilayah_id'] = 'Wilayah tidak ditemukan.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $newUser = new NewUser(
            $this->cipher->encrypt($rawNipNik),
            $this->cipher->blindIndex($rawNipNik),
            $nama,
            $finalInstansi,
            $this->cipher->encrypt((string) $waNormal),
            $rawEmail,
            password_hash($rawPassword, PASSWORD_DEFAULT),
            $roleStr,
            $wilayahId,
            $status
        );

        return $this->users->create($newUser);
    }

    private static function normalizeWa(string $raw): ?string
    {
        $clean = str_replace([' ', '-', '.', '(', ')'], '', trim($raw));
        if (str_starts_with($clean, '+62')) {
            $clean = '62' . substr($clean, 3);
        } elseif (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        if (preg_match('/^62\d{8,13}$/', $clean) === 1) {
            return $clean;
        }

        return null;
    }

    /**
     * @param string|null $status
     * @return array<int, array<string, mixed>>
     * @throws ValidationException
     */
    public function listAccounts(?string $status): array
    {
        if ($status !== null && !in_array($status, StatusAkun::ALL, true)) {
            throw new ValidationException(['status' => 'Status tidak valid.']);
        }

        $users = $this->users->findByStatus($status);
        $result = [];

        foreach ($users as $user) {
            $data = $user->jsonSerialize();
            $data['nip_nik'] = $this->cipher->decrypt($user->getNipNikEncrypted());
            $data['no_wa'] = $this->cipher->decrypt($user->getNoWaEncrypted());
            $result[] = $data;
        }

        return $result;
    }

    /**
     * @throws UserNotFoundException
     * @throws AccountConflictException
     * @throws ValidationException
     */
    public function approve(int $id, ?string $correctedRole = null): void
    {
        $this->transition(
            $id,
            [StatusAkun::MENUNGGU_VERIFIKASI, StatusAkun::DITOLAK],
            StatusAkun::AKTIF,
            $correctedRole
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws AccountConflictException
     * @throws ValidationException
     */
    public function reject(int $id): void
    {
        $this->transition(
            $id,
            [StatusAkun::MENUNGGU_VERIFIKASI],
            StatusAkun::DITOLAK
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws AccountConflictException
     * @throws ValidationException
     */
    public function deactivate(int $id): void
    {
        $this->transition(
            $id,
            [StatusAkun::AKTIF],
            StatusAkun::NONAKTIF
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws AccountConflictException
     * @throws ValidationException
     */
    public function reactivate(int $id): void
    {
        $this->transition(
            $id,
            [StatusAkun::NONAKTIF],
            StatusAkun::AKTIF
        );
    }

    /**
     * @param int $id
     * @param array<int, string> $allowedFrom
     * @param string $to
     * @param string|null $correctedRole
     * @throws UserNotFoundException
     * @throws AccountConflictException
     * @throws ValidationException
     */
    private function transition(int $id, array $allowedFrom, string $to, ?string $correctedRole = null): void
    {
        $user = $this->users->findById($id);

        if ($user->getRole() === Role::SUPERADMIN) {
            throw new AccountConflictException('Akun superadmin tidak dapat diubah melalui antarmuka.');
        }

        if (!in_array($user->getStatusAkun(), $allowedFrom, true)) {
            throw new AccountConflictException('Status akun saat ini tidak mengizinkan tindakan ini.');
        }

        if ($correctedRole !== null) {
            if (!in_array($correctedRole, Role::PUBLIC_REGISTRATION, true)) {
                throw new ValidationException(['role' => 'Peran koreksi tidak valid.']);
            }

            if (!in_array($user->getRole(), Role::PUBLIC_REGISTRATION, true)) {
                throw new AccountConflictException('Peran akun internal tidak dapat dikoreksi.');
            }

            if (
                in_array($correctedRole, Role::FIELD_REPORTERS, true)
                && ($user->getInstansi() === null || trim($user->getInstansi()) === '')
            ) {
                throw new ValidationException(['role' => 'Peran ini memerlukan instansi pada akun.']);
            }
        }

        $success = $this->users->transitionStatus($id, $user->getStatusAkun(), $to, $correctedRole);
        if (!$success) {
            throw new AccountConflictException('Status akun sudah berubah. Muat ulang data.');
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return int
     * @throws ValidationException
     * @throws DuplicateUserException
     */
    public function createInternal(array $input): int
    {
        return $this->createAccount(
            $input,
            $input['role'] ?? null,
            Role::ADMIN_CREATABLE,
            StatusAkun::AKTIF
        );
    }

    /**
     * @param array<string, mixed> $input
     * @return int
     * @throws ValidationException
     * @throws DuplicateUserException
     */
    public function createSuperadmin(array $input): int
    {
        return $this->createAccount(
            $input,
            Role::SUPERADMIN,
            [Role::SUPERADMIN],
            StatusAkun::AKTIF
        );
    }
}

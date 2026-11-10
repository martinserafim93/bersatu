<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\User\DuplicateUserException;
use App\Domain\User\NewUser;
use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository;

class FakeUserRepository implements UserRepository
{
    /** @var array<int, User> */
    private array $users = [];
    /** @var array<string, int> */
    private array $emailIndex = [];
    /** @var array<string, int> */
    private array $hashIndex = [];
    private int $nextId = 1;

    public function findById(int $id): User
    {
        if (!isset($this->users[$id])) {
            throw new UserNotFoundException();
        }

        return $this->users[$id];
    }

    public function findByEmail(string $email): ?User
    {
        $normalized = mb_strtolower(trim($email));
        if (!isset($this->emailIndex[$normalized])) {
            return null;
        }

        $id = $this->emailIndex[$normalized];
        return $this->users[$id] ?? null;
    }

    public function findByStatus(?string $status): array
    {
        $result = [];
        foreach ($this->users as $user) {
            if ($status === null || $user->getStatusAkun() === $status) {
                $result[] = $user;
            }
        }

        return $result;
    }

    public function create(NewUser $user): int
    {
        $emailNorm = mb_strtolower(trim($user->email));
        if (isset($this->emailIndex[$emailNorm]) || isset($this->hashIndex[$user->nipNikHash])) {
            throw new DuplicateUserException();
        }

        $id = $this->nextId++;
        $createdUser = new User(
            $id,
            $user->namaLengkap,
            $user->instansi,
            $user->email,
            $user->passwordHash,
            $user->role,
            $user->wilayahId,
            $user->statusAkun,
            $user->nipNikEncrypted,
            $user->noWaEncrypted,
            date('Y-m-d H:i:s')
        );

        $this->users[$id] = $createdUser;
        $this->emailIndex[$emailNorm] = $id;
        $this->hashIndex[$user->nipNikHash] = $id;

        return $id;
    }

    public function transitionStatus(int $id, string $from, string $to, ?string $role = null): bool
    {
        if (!isset($this->users[$id])) {
            return false;
        }

        $current = $this->users[$id];
        if ($current->getStatusAkun() !== $from) {
            return false;
        }

        $this->users[$id] = new User(
            $current->getId(),
            $current->getNamaLengkap(),
            $current->getInstansi(),
            $current->getEmail(),
            $current->getPasswordHash(),
            $role ?? $current->getRole(),
            $current->getWilayahId(),
            $to,
            $current->getNipNikEncrypted(),
            $current->getNoWaEncrypted(),
            $current->getCreatedAt()
        );

        return true;
    }

    public function addUserDirectly(User $user, string $nipNikHash): void
    {
        $this->users[$user->getId()] = $user;
        $this->emailIndex[mb_strtolower($user->getEmail())] = $user->getId();
        $this->hashIndex[$nipNikHash] = $user->getId();
        if ($user->getId() >= $this->nextId) {
            $this->nextId = $user->getId() + 1;
        }
    }
}

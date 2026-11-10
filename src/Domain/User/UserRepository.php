<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRepository
{
    /**
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function findById(int $id): User;

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * @param string|null $status
     * @return array<int, User>
     */
    public function findByStatus(?string $status): array;

    /**
     * @param NewUser $user
     * @return int
     * @throws DuplicateUserException
     */
    public function create(NewUser $user): int;

    /**
     * @param int $id
     * @param string $from
     * @param string $to
     * @param string|null $role
     * @return bool
     */
    public function transitionStatus(int $id, string $from, string $to, ?string $role = null): bool;
}

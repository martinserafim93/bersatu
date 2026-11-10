<?php

declare(strict_types=1);

namespace App\Application\Auth;

final class AuthSession
{
    /** @var array<string, mixed> */
    private array $data;
    /** @var callable */
    private $regenerateId;
    /** @var callable */
    private $destroy;

    /**
     * @param array<string, mixed> $data
     * @param callable $regenerateId
     * @param callable $destroy
     */
    public function __construct(array &$data, callable $regenerateId, callable $destroy)
    {
        $this->data = &$data;
        $this->regenerateId = $regenerateId;
        $this->destroy = $destroy;
    }

    public function userId(): ?int
    {
        if (isset($this->data['user_id']) && is_numeric($this->data['user_id'])) {
            return (int) $this->data['user_id'];
        }

        return null;
    }

    public function login(int $userId): void
    {
        ($this->regenerateId)();
        $this->data['user_id'] = $userId;
    }

    public function logout(): void
    {
        $this->data = [];
        ($this->destroy)();
    }
}

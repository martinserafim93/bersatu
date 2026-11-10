<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

final class User implements JsonSerializable
{
    public function __construct(
        private int $id,
        private string $namaLengkap,
        private ?string $instansi,
        private string $email,
        private string $passwordHash,
        private string $role,
        private int $wilayahId,
        private string $statusAkun,
        private string $nipNikEncrypted,
        private string $noWaEncrypted,
        private string $createdAt
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNamaLengkap(): string
    {
        return $this->namaLengkap;
    }

    public function getInstansi(): ?string
    {
        return $this->instansi;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getWilayahId(): int
    {
        return $this->wilayahId;
    }

    public function getStatusAkun(): string
    {
        return $this->statusAkun;
    }

    public function getNipNikEncrypted(): string
    {
        return $this->nipNikEncrypted;
    }

    public function getNoWaEncrypted(): string
    {
        return $this->noWaEncrypted;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nama_lengkap' => $this->namaLengkap,
            'instansi' => $this->instansi,
            'email' => $this->email,
            'role' => $this->role,
            'wilayah_id' => $this->wilayahId,
            'status_akun' => $this->statusAkun,
            'created_at' => $this->createdAt,
        ];
    }
}

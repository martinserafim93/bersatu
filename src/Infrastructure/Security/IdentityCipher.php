<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use InvalidArgumentException;
use RuntimeException;

final class IdentityCipher
{
    private string $encKey;
    private string $indexKey;

    public function __construct(string $hexKey)
    {
        if (preg_match('/^[0-9a-fA-F]{64}$/', $hexKey) !== 1) {
            throw new InvalidArgumentException(
                'APP_ENCRYPTION_KEY harus 64 karakter heksadesimal (32 byte).'
                . ' Buat dengan: php -r "echo bin2hex(random_bytes(32));"'
            );
        }

        $master = hex2bin($hexKey);
        if ($master === false) {
            throw new InvalidArgumentException('Kunci heksadesimal tidak valid.');
        }

        $this->encKey = hash_hkdf('sha256', $master, 32, 'bersatu-aes-256-gcm');
        $this->indexKey = hash_hkdf('sha256', $master, 32, 'bersatu-blind-index');
    }

    public function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $this->encKey, OPENSSL_RAW_DATA, $iv, $tag, '', 16);

        if ($cipher === false) {
            throw new RuntimeException('Enkripsi gagal.');
        }

        return base64_encode($iv . $tag . $cipher);
    }

    public function decrypt(string $payload): string
    {
        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < 28) {
            throw new RuntimeException('Data terenkripsi tidak valid.');
        }

        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);

        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $this->encKey, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new RuntimeException('Data terenkripsi tidak valid.');
        }

        return $plain;
    }

    public function blindIndex(string $plain): string
    {
        return hash_hmac('sha256', $plain, $this->indexKey);
    }
}

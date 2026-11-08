<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Database;

use PDO;

class PdoConnectionFactory
{
    /**
     * @param array<string, mixed> $dbSettings
     */
    public function buildDsn(array $dbSettings): string
    {
        $driver = (string) ($dbSettings['driver'] ?? 'mysql');

        if ($driver === 'sqlite') {
            $database = (string) ($dbSettings['database'] ?? ':memory:');
            return 'sqlite:' . ($database !== '' ? $database : ':memory:');
        }

        $host = (string) ($dbSettings['host'] ?? '127.0.0.1');
        $port = (int) ($dbSettings['port'] ?? 3306);
        $database = (string) ($dbSettings['database'] ?? '');
        $charset = (string) ($dbSettings['charset'] ?? 'utf8mb4');

        return sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);
    }

    /**
     * @param array<string, mixed> $dbSettings
     */
    public function create(array $dbSettings): PDO
    {
        $driver = (string) ($dbSettings['driver'] ?? 'mysql');
        $dsn = $this->buildDsn($dbSettings);
        /** @var array<int, mixed> $flags */
        $flags = $dbSettings['flags'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($driver === 'sqlite') {
            return new PDO($dsn, null, null, $flags);
        }

        $username = (string) ($dbSettings['username'] ?? 'root');
        $password = (string) ($dbSettings['password'] ?? '');

        return new PDO($dsn, $username, $password, $flags);
    }
}

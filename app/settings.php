<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'db' => [
                    'driver' => (string) ($_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'mysql'),
                    'host' => (string) ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1'),
                    'port' => (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306),
                    'database' => (string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'bersatu'),
                    'username' => (string) ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root'),
                    'password' => (string) ($_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: ''),
                    'charset' => (string) ($_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4'),
                    'flags' => [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ],
                ],
            ]);
        }
    ]);
};

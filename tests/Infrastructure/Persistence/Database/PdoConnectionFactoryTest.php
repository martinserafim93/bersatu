<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Database;

use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Persistence\Database\PdoConnectionFactory;
use PDO;
use Tests\TestCase;

class PdoConnectionFactoryTest extends TestCase
{
    public function testSettingsContainsDbConfiguration(): void
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        $this->assertNotNull($container);

        /** @var SettingsInterface $settings */
        $settings = $container->get(SettingsInterface::class);
        $db = $settings->get('db');

        $this->assertIsArray($db);
        $this->assertSame('mysql', $db['driver']);
        $this->assertSame('127.0.0.1', $db['host']);
        $this->assertSame(3306, $db['port']);
        $this->assertSame('bersatu', $db['database']);
        $this->assertSame('root', $db['username']);
        $this->assertSame('utf8mb4', $db['charset']);
        $this->assertArrayHasKey('flags', $db);
    }

    public function testBuildDsnForMysql(): void
    {
        $factory = new PdoConnectionFactory();
        $dsn = $factory->buildDsn([
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3307,
            'database' => 'portal_db',
            'charset' => 'utf8mb4',
        ]);

        $this->assertSame('mysql:host=localhost;port=3307;dbname=portal_db;charset=utf8mb4', $dsn);
    }

    public function testBuildDsnForSqlite(): void
    {
        $factory = new PdoConnectionFactory();
        $dsn = $factory->buildDsn([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->assertSame('sqlite::memory:', $dsn);
    }

    public function testCreateSqliteConnection(): void
    {
        $factory = new PdoConnectionFactory();
        $pdo = $factory->create([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertSame('sqlite', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
    }
}

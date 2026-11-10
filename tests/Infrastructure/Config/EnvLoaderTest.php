<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Config;

use App\Infrastructure\Config\EnvLoader;
use Tests\TestCase;

class EnvLoaderTest extends TestCase
{
    private string $tempEnvPath;
    /** @var array<string, string|false> */
    private array $envBackup = [];
    /** @var array<string, mixed> */
    private array $serverEnvBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempEnvPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_' . uniqid('', true) . '.env';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempEnvPath)) {
            @unlink($this->tempEnvPath);
        }

        foreach ($this->envBackup as $key => $val) {
            if ($val === false) {
                putenv($key);
                unset($_ENV[$key]);
            } else {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
            }
        }

        parent::tearDown();
    }

    private function trackEnv(string $key): void
    {
        if (!array_key_exists($key, $this->envBackup)) {
            $this->envBackup[$key] = getenv($key);
        }
    }

    public function testLoadParsesPairsAndIgnoresCommentsAndWhitespace(): void
    {
        $this->trackEnv('TEST_LOAD_A');
        $this->trackEnv('TEST_LOAD_B');
        $this->trackEnv('TEST_LOAD_C');

        $content = <<<ENV
# Komentar awal baris
TEST_LOAD_A=nilai_a

   # Komentar dengan spasi
TEST_LOAD_B="nilai dengan spasi"
TEST_LOAD_C='nilai kutip tunggal'
BARIS_TANPA_SAMA_DENGAN
ENV;
        file_put_contents($this->tempEnvPath, $content);

        EnvLoader::load($this->tempEnvPath);

        $this->assertSame('nilai_a', $_ENV['TEST_LOAD_A'] ?? null);
        $this->assertSame('nilai_a', getenv('TEST_LOAD_A'));
        $this->assertSame('nilai dengan spasi', $_ENV['TEST_LOAD_B'] ?? null);
        $this->assertSame('nilai kutip tunggal', $_ENV['TEST_LOAD_C'] ?? null);
    }

    public function testLoadDoesNotOverwriteExistingEnvironmentVariables(): void
    {
        $this->trackEnv('TEST_EXISTING_KEY');
        putenv('TEST_EXISTING_KEY=nilai_lama');
        $_ENV['TEST_EXISTING_KEY'] = 'nilai_lama';

        file_put_contents($this->tempEnvPath, "TEST_EXISTING_KEY=nilai_baru\n");

        EnvLoader::load($this->tempEnvPath);

        $this->assertSame('nilai_lama', getenv('TEST_EXISTING_KEY'));
        $this->assertSame('nilai_lama', $_ENV['TEST_EXISTING_KEY']);
    }

    public function testLoadHandlesNonExistentFileGracefully(): void
    {
        $nonExistent = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'definitely_missing_' . uniqid('', true) . '.env';
        EnvLoader::load($nonExistent);
        $this->assertFileDoesNotExist($nonExistent);
    }
}

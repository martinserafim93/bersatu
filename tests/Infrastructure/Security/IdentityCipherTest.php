<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Security;

use App\Infrastructure\Security\IdentityCipher;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class IdentityCipherTest extends TestCase
{
    private string $testKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testKey = str_repeat('ab', 32);
    }

    public function testDecryptReturnsOriginalPlaintext(): void
    {
        $cipher = new IdentityCipher($this->testKey);
        $original = '3174012345678901';

        $encrypted = $cipher->encrypt($original);
        $this->assertNotSame($original, $encrypted);

        $decrypted = $cipher->decrypt($encrypted);
        $this->assertSame($original, $decrypted);
    }

    public function testEncryptProducesDifferentCiphertextEachCall(): void
    {
        $cipher = new IdentityCipher($this->testKey);
        $plain = '081234567890';

        $enc1 = $cipher->encrypt($plain);
        $enc2 = $cipher->encrypt($plain);

        $this->assertNotSame($enc1, $enc2);
        $this->assertSame($plain, $cipher->decrypt($enc1));
        $this->assertSame($plain, $cipher->decrypt($enc2));
    }

    public function testBlindIndexIsDeterministicAnd64HexChars(): void
    {
        $cipher = new IdentityCipher($this->testKey);
        $idx1 = $cipher->blindIndex('3174012345678901');
        $idx2 = $cipher->blindIndex('3174012345678901');
        $idxOther = $cipher->blindIndex('3174012345678902');

        $this->assertSame($idx1, $idx2);
        $this->assertSame(64, strlen($idx1));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $idx1);
        $this->assertNotSame($idx1, $idxOther);
    }

    public function testTamperedPayloadThrowsRuntimeException(): void
    {
        $cipher = new IdentityCipher($this->testKey);
        $encrypted = $cipher->encrypt('rahasia-sensitif');

        $raw = base64_decode($encrypted, true);
        $this->assertIsString($raw);
        $tamperedRaw = $raw;
        $tamperedRaw[strlen($tamperedRaw) - 1] = chr(ord($tamperedRaw[strlen($tamperedRaw) - 1]) ^ 0xFF);
        $tamperedPayload = base64_encode($tamperedRaw);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data terenkripsi tidak valid.');
        $cipher->decrypt($tamperedPayload);
    }

    public function testPayloadTooShortThrowsRuntimeException(): void
    {
        $cipher = new IdentityCipher($this->testKey);
        $tooShort = base64_encode(str_repeat('x', 20));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data terenkripsi tidak valid.');
        $cipher->decrypt($tooShort);
    }

    /**
     * @dataProvider invalidKeyProvider
     */
    public function testInvalidKeyThrowsInvalidArgumentException(string $invalidKey): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IdentityCipher($invalidKey);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function invalidKeyProvider(): array
    {
        return [
            'empty' => [''],
            'too_short' => [str_repeat('a', 63)],
            'too_long' => [str_repeat('a', 65)],
            'non_hex' => [str_repeat('z', 64)],
        ];
    }

    public function testDifferentKeyCannotDecryptPayload(): void
    {
        $cipher1 = new IdentityCipher($this->testKey);
        $key2 = str_repeat('cd', 32);
        $cipher2 = new IdentityCipher($key2);

        $payload = $cipher1->encrypt('pesan-rahasia');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data terenkripsi tidak valid.');
        $cipher2->decrypt($payload);
    }
}

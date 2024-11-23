<?php

namespace HelloCoop\Tests;

use PHPUnit\Framework\TestCase;
use HelloCoop\Lib\Crypto;
use HelloCoop\Exception\InvalidSecretException;
use HelloCoop\Exception\DecryptionFailedException;
use HelloCoop\Exception\CryptoFailedException;

class CryptoTest extends TestCase
{
    private string $validSecret = '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef'; // 64 hex characters (32 bytes)
    private string $invalidSecret = 'invalidsecret'; // Invalid secret
    private Crypto $crypto;

    public function setUp(): void
    {
        // Set up a valid Crypto instance for tests
        $this->crypto = new Crypto($this->validSecret);
    }

    public function testConstructorWithValidSecret(): void
    {
        $this->assertInstanceOf(Crypto::class, $this->crypto);
    }

    public function testConstructorWithInvalidSecret(): void
    {
        $this->expectException(InvalidSecretException::class);
        new Crypto($this->invalidSecret);
    }

    public function testEncryptDecrypt(): void
    {
        $data = ['key' => 'value', 'number' => 123];

        // Encrypt the data
        $encrypted = $this->crypto->encrypt($data);
        $this->assertNotEmpty($encrypted, 'Encrypted string should not be empty.');

        // Decrypt the data
        $decrypted = $this->crypto->decrypt($encrypted);
        $this->assertNotNull($decrypted, 'Decrypted data should not be null.');
        $this->assertEquals($data, $decrypted, 'Decrypted data should match the original data.');
    }

    public function testDecryptWithInvalidData(): void
    {
        $invalidEncryptedData = 'invalid_base64_string';
        $this->expectException(DecryptionFailedException::class);
        $this->crypto->decrypt($invalidEncryptedData);
    }

    public function testEncryptWithInvalidData(): void
    {
        $this->expectException(CryptoFailedException::class);
        $this->crypto->encrypt(['key' => "\x80\x81\x82"]); // Invalid data
    }

    public function testCheckSecretWithValidSecret(): void
    {
        $result = $this->crypto->checkSecret($this->validSecret);
        $this->assertTrue($result, 'checkSecret should return true for valid secret.');
    }

    public function testCheckSecretWithInvalidSecret(): void
    {
        $result = $this->crypto->checkSecret($this->invalidSecret);
        $this->assertFalse($result, 'checkSecret should return false for invalid secret.');
    }
}

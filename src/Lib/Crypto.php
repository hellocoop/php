<?php

namespace HelloCoop\Lib;

use HelloCoop\Exception\InvalidSecretException;
use HelloCoop\Exception\DecryptionFailedException;
use HelloCoop\Exception\CryptoFailedException;
use Exception;

class Crypto
{
    private string $secret;

    public function __construct(?string $secret)
    {
        if (!$this->checkSecret($secret)) {
            throw new InvalidSecretException();
        }
        $this->secret = hex2bin($secret);
    }

    public function encrypt(array $data): string
    {
        $jsonData = json_encode($data);
        if ($jsonData === false) {
            throw new CryptoFailedException();
        }

        $iv = random_bytes(12);
        $key = $this->secret;
        $cipher = openssl_encrypt($jsonData, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($cipher === false) {
            throw new CryptoFailedException();
        }

        $encryptedData = $iv . $cipher . $tag;
        return $this->uint8ArrayToUrlSafeBase64($encryptedData);
    }

    public function decrypt(string $encryptedStr): ?array
    {
        try {
            $encryptedData = $this->urlSafeBase64ToUint8Array($encryptedStr);
            $iv = substr($encryptedData, 0, 12);
            $tag = substr($encryptedData, -16);
            $ciphertext = substr($encryptedData, 12, -16);

            $key = $this->secret;
            $decryptedData = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

            if ($decryptedData === false) {
                throw new DecryptionFailedException();
            }

            return json_decode($decryptedData, true);
        } catch (Exception $e) {
            throw new DecryptionFailedException();
        }
    }

    public function checkSecret($secret): bool
    {
        if (!ctype_xdigit($secret) || strlen($secret) % 2 != 0) {
            return false;
        }
        $key = hex2bin($secret);
        return $key !== false && strlen($key) === 32;
    }

    private function uint8ArrayToUrlSafeBase64(string $binaryData): string
    {
        return rtrim(strtr(base64_encode($binaryData), '+/', '-_'), '=');
    }

    private function urlSafeBase64ToUint8Array(string $base64String): string
    {
        $base64 = strtr($base64String, '-_', '+/');
        $binaryData = base64_decode($base64 . str_repeat('=', (4 - strlen($base64) % 4) % 4));
        return $binaryData;
    }
}

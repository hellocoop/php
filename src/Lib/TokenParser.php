<?php

namespace HelloCoop\Lib;

class TokenParser
{
    public function parseToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid token format.');
        }

        [$headerEncoded, $payloadEncoded] = $parts;

        $headerJSON = self::base64UrlDecode($headerEncoded);
        $payloadJSON = self::base64UrlDecode($payloadEncoded);

        try {
            $header = json_decode($headerJSON, true, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode($payloadJSON, true, 512, JSON_THROW_ON_ERROR);

            // TODO - Add logic to validate 'typ' header
            // TODO - Add logic to ensure 'exp' claim is present

            return [
                'header' => $header,
                'payload' => $payload,
            ];
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to parse token: ' . $e->getMessage());
        }
    }

    private function base64UrlDecode(string $data): string
    {
        $decodedData = strtr($data, '-_', '+/');
        return base64_decode($decodedData, true);
    }
}

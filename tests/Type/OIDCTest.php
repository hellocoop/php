<?php

namespace HelloCoop\Tests\Type;

use HelloCoop\Type\OIDC;
use PHPUnit\Framework\TestCase;

class OIDCTest extends TestCase
{
    public function testFromArrayValidData(): void
    {
        $data = [
            'code_verifier' => 'test_verifier',
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '/home',
        ];

        $oidc = OIDC::fromArray($data);

        $this->assertInstanceOf(OIDC::class, $oidc);
        $this->assertEquals('test_verifier', $oidc->codeVerifier);
        $this->assertEquals('test_nonce', $oidc->nonce);
        $this->assertEquals('https://example.com/callback', $oidc->redirectUri);
        $this->assertEquals('/home', $oidc->targetUri);
    }

    public function testFromArrayMissingKeys(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing code_verifier');

        $data = [
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '/home',
        ];

        OIDC::fromArray($data);
    }

    public function testToArray(): void
    {
        $oidc = new OIDC('test_verifier', 'test_nonce', 'https://example.com/callback', '/home');

        $expected = [
            'code_verifier' => 'test_verifier',
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '/home',
        ];

        $this->assertEquals($expected, $oidc->toArray());
    }
}

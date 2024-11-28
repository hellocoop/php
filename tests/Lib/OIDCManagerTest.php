<?php

namespace HelloCoop\Tests;

use HelloCoop\Lib\OIDCManager;
use HelloCoop\Type\OIDC;
use HelloCoop\Cookie\CookieManagerInterface;
use HelloCoop\Lib\Crypto;
use PHPUnit\Framework\TestCase;

class OIDCManagerTest extends TestCase {
    private $cookieManager;
    private $crypto;
    private $oidcManager;
    private $config;

    protected function setUp(): void {
        $this->cookieManager = $this->createMock(CookieManagerInterface::class);
        $this->crypto = $this->createMock(Crypto::class);
        $this->config = [
            'production' => true,
            'sameSiteStrict' => true,
        ];

        $this->oidcManager = new OIDCManager(
            $this->cookieManager, 
            $this->crypto, 
            'oidc_cookie', 
            $this->config,
            '/path'
        );
    }

    public function testGetOidcValid(): void {
        $oidcData = [
            'code_verifier' => 'test_verifier',
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '/home',
        ];

        $this->cookieManager->method('get')->with('oidc_cookie')->willReturn('encrypted_cookie');
        $this->crypto->method('decrypt')->with('encrypted_cookie')->willReturn($oidcData);

        $oidc = $this->oidcManager->getOidc();

        $this->assertInstanceOf(OIDC::class, $oidc);
        $this->assertEquals('test_verifier', $oidc->codeVerifier);
    }

    public function testGetOidcInvalid(): void {
        $this->cookieManager->method('get')->with('oidc_cookie')->willReturn(null);

        $oidc = $this->oidcManager->getOidc();

        $this->assertNull($oidc);
    }

    public function testSaveOidc(): void {
        $oidc = new OIDC('test_verifier', 'test_nonce', 'https://example.com/callback', '/home');

        $this->crypto->method('encrypt')->with($oidc->toArray())->willReturn('encrypted_cookie');

        $this->cookieManager->expects($this->once())
            ->method('set')
            ->with(
                'oidc_cookie',
                'encrypted_cookie',
                $this->greaterThan(0), // maxAge calculation, non-zero expiry
                '/path',
                '',
                true,  // secure flag
                true   // httponly flag
            );

        $this->oidcManager->saveOidc($oidc);
    }

    public function testClearOidcCookie(): void {
        $this->cookieManager->expects($this->once())
            ->method('delete')
            ->with('oidc_cookie', '/path', '');

        $this->oidcManager->clearOidcCookie();
    }
}

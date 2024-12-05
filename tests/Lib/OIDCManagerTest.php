<?php

namespace HelloCoop\Tests;

use HelloCoop\Lib\OIDCManager;
use HelloCoop\Type\OIDC;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Lib\Crypto;
use PHPUnit\Framework\TestCase;

class OIDCManagerTest extends TestCase
{
    private $cookieManager;
    private $crypto;
    private $helloRequestMock;
    private $helloResponseMock;
    private $oidcManager;
    private $config;

    protected function setUp(): void
    {
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->helloResponseMock = $this->createMock(HelloResponseInterface::class);
        $this->crypto = $this->createMock(Crypto::class);
        $this->config = [
            'production' => true,
            'sameSiteStrict' => true,
        ];

        $this->oidcManager = new OIDCManager(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->crypto,
            'oidc_cookie',
            $this->config,
            '/path'
        );
    }

    public function testGetOidcValid(): void
    {
        $oidcData = [
            'code_verifier' => 'test_verifier',
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '/home',
        ];

        $this->helloRequestMock->method('getCookie')->with('oidc_cookie')->willReturn('encrypted_cookie');
        $this->crypto->method('decrypt')->with('encrypted_cookie')->willReturn($oidcData);

        $oidc = $this->oidcManager->getOidc();

        $this->assertInstanceOf(OIDC::class, $oidc);
        $this->assertEquals('test_verifier', $oidc->codeVerifier);
    }

    public function testGetOidcInvalid(): void
    {
        $this->helloRequestMock->method('getCookie')->with('oidc_cookie')->willReturn(null);

        $oidc = $this->oidcManager->getOidc();

        $this->assertNull($oidc);
    }

    public function testSaveOidc(): void
    {
        $oidc = new OIDC('test_verifier', 'test_nonce', 'https://example.com/callback', '/home');

        $this->crypto->method('encrypt')->with($oidc->toArray())->willReturn('encrypted_cookie');

        $this->helloResponseMock->expects($this->once())
            ->method('setCookie')
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

    public function testClearOidcCookie(): void
    {
        $this->helloResponseMock->expects($this->once())
            ->method('deleteCookie')
            ->with('oidc_cookie', '/path', '');

        $this->oidcManager->clearOidcCookie();
    }
}

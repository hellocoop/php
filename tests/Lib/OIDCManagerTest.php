<?php

namespace HelloCoop\Tests;

use HelloCoop\Lib\OIDCManager;
use HelloCoop\Type\OIDC;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Lib\Crypto;
use HelloCoop\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class OIDCManagerTest extends TestCase
{
    /** @var MockObject & HelloRequestInterface */
    private $helloRequestMock;
    /** @var MockObject & HelloResponseInterface */
    private $helloResponseMock;
    /** @var MockObject & Crypto */
    private $cryptoMock;
    /** @var OIDCManager */
    private OIDCManager $oidcManager;
    /** @var MockObject & ConfigInterface */
    private $configMock;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->helloResponseMock = $this->createMock(HelloResponseInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->cryptoMock = $this->createMock(Crypto::class);

        // Setting up mock behaviors for config
        $this->configMock->method('getCookies')->willReturn(['oidcName' => 'oidc_cookie']);
        $this->configMock->method('getApiRoute')->willReturn('/path');
        $this->configMock->method('getProduction')->willReturn(true);

        // Create the OIDCManager instance
        $this->oidcManager = new OIDCManager(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->configMock,
            $this->cryptoMock
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

        // Mock the behavior of getCookie
        $this->helloRequestMock->expects($this->once())
            ->method('getCookie')
            ->with('oidc_cookie')
            ->willReturn('encrypted_cookie');

        // Mock the decrypt method to return valid OIDC data
        $this->cryptoMock->expects($this->once())
            ->method('decrypt')
            ->with('encrypted_cookie')
            ->willReturn($oidcData);

        $oidc = $this->oidcManager->getOidc();

        $this->assertInstanceOf(OIDC::class, $oidc);
        $this->assertEquals('test_verifier', $oidc->codeVerifier);
    }

    public function testSaveOidc(): void
    {
        $oidc = new OIDC(
            'test_verifier',
            'test_nonce',
            'https://example.com/callback',
            '/home'
        );

        // Mock the encrypt method to return a valid encrypted cookie
        $this->cryptoMock->expects($this->once())
            ->method('encrypt')
            ->with($oidc->toArray())
            ->willReturn('encrypted_cookie');

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

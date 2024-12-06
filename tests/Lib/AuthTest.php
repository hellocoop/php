<?php

namespace HelloCoop\Tests;

use PHPUnit\Framework\TestCase;
use HelloCoop\Lib\Auth;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Lib\OIDCManager;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Lib\Crypto;
use HelloCoop\Config\ConfigInterface;
use Exception;

class AuthTest extends TestCase
{
    private Auth $auth;
    private $cryptoMock;
    private $helloRequestMock;
    private $helloResponseMock;
    private $configMock;
    private OIDCManager $oidcManager;

    protected function setUp(): void
    {
        $this->cryptoMock = $this->createMock(Crypto::class);
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->helloResponseMock = $this->createMock(HelloResponseInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->oidcManager = $this->createMock(OIDCManager::class);

        // Mock config to return cookie names and other config values
        $this->configMock->method('getCookies')->willReturn([
            'authName' => 'auth_name',
            'oidcName' => 'oidc_name'
        ]);
        $this->configMock->method('getCookieToken')->willReturn(true);

        $this->auth = new Auth(
            $this->cryptoMock,
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->oidcManager,
            $this->configMock
        );
    }

    public function testSaveAuthCookieSuccess()
    {
        $authMock = $this->createMock(AuthType::class);
        $this->cryptoMock->method('encrypt')->willReturn('encrypted_cookie');

        $this->helloResponseMock
            ->expects($this->once())
            ->method('setCookie')
            ->with('auth_name', 'encrypted_cookie');

        $result = $this->auth->saveAuthCookie($authMock);
        $this->assertTrue($result);
    }

    public function testSaveAuthCookieEncryptionFails()
    {
        $authMock = $this->createMock(AuthType::class);
        $this->cryptoMock->method('encrypt')->willReturn('');

        $this->helloResponseMock->expects($this->never())->method('setCookie');

        $result = $this->auth->saveAuthCookie($authMock);
        $this->assertFalse($result);
    }

    public function testGetAuthFromCookiesSuccess()
    {
        // Simulate cookies
        $this->helloRequestMock->method('getCookie')->willReturn('encrypted_cookie');
        $this->cryptoMock->method('decrypt')->willReturn([
            'isLoggedIn' => true,
            'authData' => ['sub' => 'user123', 'iat' => time()]
        ]);

        $this->helloResponseMock->expects($this->never())->method('deleteCookie');

        $auth = $this->auth->getAuthfromCookies();
        $this->assertInstanceOf(AuthType::class, $auth);
        $this->assertTrue($auth->isLoggedIn);
    }

    public function testGetAuthFromCookiesDecryptFails()
    {
        // Simulate cookies
        $this->helloRequestMock->method('getCookie')->willReturn('encrypted_cookie');
        $this->cryptoMock->method('decrypt')->willThrowException(new Exception());

        $this->helloResponseMock
            ->expects($this->once())
            ->method('deleteCookie')
            ->with('auth_name');

        $auth = $this->auth->getAuthfromCookies();
        $this->assertFalse($auth->isLoggedIn);
    }
}

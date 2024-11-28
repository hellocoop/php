<?php

namespace HelloCoop\Tests;

use PHPUnit\Framework\TestCase;
use HelloCoop\Lib\Auth;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Lib\OIDCManager;
use HelloCoop\Cookie\CookieManagerInterface;
use HelloCoop\Lib\Crypto;
use Exception;

class AuthTest extends TestCase
{
    private Auth $auth;
    private $cryptoMock;
    private $cookieManagerMock;

    private OIDCManager $oidcManager;

    protected function setUp(): void
    {
        $this->cryptoMock = $this->createMock(Crypto::class);
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->oidcManager = $this->createMock(OIDCManager::class);
        $this->auth = new Auth(
            'oidc_name',
            'auth_name',
            $this->cryptoMock,
            $this->cookieManagerMock,
            $this->oidcManager,
            'some_cookie_token'
        );
    }

    public function testSaveAuthCookieSuccess()
    {
        $authMock = $this->createMock(AuthType::class);
        $this->cryptoMock->method('encrypt')->willReturn('encrypted_cookie');

        $this->cookieManagerMock
            ->expects($this->once())
            ->method('set')
            ->with('auth_name', 'encrypted_cookie');

        $result = $this->auth->saveAuthCookie($authMock);
        $this->assertTrue($result);
    }

    public function testSaveAuthCookieEncryptionFails()
    {
        $authMock = $this->createMock(AuthType::class);
        $this->cryptoMock->method('encrypt')->willReturn(false);

        $this->cookieManagerMock->expects($this->never())->method('set');

        $result = $this->auth->saveAuthCookie($authMock);
        $this->assertFalse($result);
    }

    public function testGetAuthFromCookiesSuccess()
    {
        $_SERVER['HTTP_COOKIE'] = 'auth_name=encrypted_cookie';
        $this->cryptoMock->method('decrypt')->willReturn([
            'isLoggedIn' => true,
            'authData' => ['sub' => 'user123', 'iat' => time()]
        ]);

        $this->cookieManagerMock->expects($this->never())->method('delete');

        $auth = $this->auth->getAuthfromCookies();
        $this->assertInstanceOf(AuthType::class, $auth);
    }

    public function testGetAuthFromCookiesDecryptFails()
    {
        $_SERVER['HTTP_COOKIE'] = 'auth_name=encrypted_cookie';
        $this->cryptoMock->method('decrypt')->willThrowException(new Exception());

        $this->cookieManagerMock
            ->expects($this->once())
            ->method('delete')
            ->with(['auth_name']);

        $auth = $this->auth->getAuthfromCookies();
        $this->assertNull($auth);
    }
}
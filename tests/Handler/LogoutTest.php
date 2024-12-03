<?php

namespace HelloCoop\Tests\Handler;

use HelloCoop\Config\HelloConfig;
use HelloCoop\Handler\Logout;
use HelloCoop\Lib\Auth;
use HelloCoop\RequestParamFetcher\ParamFetcherInterface;
use PHPUnit\Framework\TestCase;

class LogoutTest extends TestCase
{
    private $configMock;
    private $paramFetcherMock;
    private $authMock;
    private $logoutHandler;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(HelloConfig::class);
        $this->paramFetcherMock = $this->createMock(ParamFetcherInterface::class);
        $this->authMock = $this->createMock(Auth::class);

        $this->logoutHandler = new Logout(
            $this->configMock,
            $this->paramFetcherMock,
            $this->authMock
        );
    }

    public function testGenerateLogoutUrlWithTargetUri(): void
    {
        $this->paramFetcherMock
            ->method('fetch')
            ->with('target_uri')
            ->willReturn('http://example.com/target');

        $this->authMock
            ->expects($this->once())
            ->method('clearAuthCookie');

        $this->configMock
            ->method('getLoginSync')
            ->willReturn(null);

        $this->configMock
            ->method('getRoutes')
            ->willReturn(['loggedOut' => 'http://example.com/logout']);

        $result = $this->logoutHandler->generateLogoutUrl();

        $this->assertEquals('http://example.com/target', $result);
    }

    public function testGenerateLogoutUrlWithoutTargetUri(): void
    {
        $this->paramFetcherMock
            ->method('fetch')
            ->with('target_uri')
            ->willReturn(null);

        $this->authMock
            ->expects($this->once())
            ->method('clearAuthCookie');

        $this->configMock
            ->method('getLoginSync')
            ->willReturn(null);

        $this->configMock
            ->method('getRoutes')
            ->willReturn(['loggedOut' => 'http://example.com/logout']);

        $result = $this->logoutHandler->generateLogoutUrl();

        $this->assertEquals('http://example.com/logout', $result);
    }

    public function testGenerateLogoutUrlWithLoginSync(): void
    {
        $this->paramFetcherMock
            ->method('fetch')
            ->with('target_uri')
            ->willReturn(null);

        $this->authMock
            ->expects($this->once())
            ->method('clearAuthCookie');

        $syncCallback = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();

        $syncCallback->expects($this->once())
            ->method('__invoke');

        $this->configMock
            ->method('getLoginSync')
            ->willReturn($syncCallback);

        $this->configMock
            ->method('getRoutes')
            ->willReturn(['loggedOut' => 'http://example.com/logout']);

        $result = $this->logoutHandler->generateLogoutUrl();

        $this->assertEquals('http://example.com/logout', $result);
    }
}

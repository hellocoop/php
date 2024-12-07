<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Auth;
use HelloCoop\Lib\Auth as AuthLib;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Type\Auth as AuthType;

class AuthTest extends TestCase
{
    private Auth $auth;
    private $authLibMock;
    private $helloRequestMock;
    private $helloResponseMock;
    private $configMock;

    public function setUp(): void
    {
        // Mock the dependencies
        $this->authLibMock = $this->createMock(AuthLib::class);
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->helloResponseMock = $this->createMock(HelloResponseInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);

        // Inject a mock of AuthLib directly into Auth (e.g., through a factory pattern or constructor)
        $this->auth = new Auth(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->configMock
        );

        // Use Reflection to inject the mocked AuthLib (bypassing getAuthLib)
        $reflection = new \ReflectionClass(Auth::class);
        $authLibProperty = $reflection->getProperty('authLib');
        $authLibProperty->setAccessible(true);
        $authLibProperty->setValue($this->auth, $this->authLibMock);
    }

    public function testCanHandleAuth(): void
    {
        $expectedAuth = $this->createMock(AuthType::class);

        $this->authLibMock
            ->expects($this->once())
            ->method('getAuthfromCookies')
            ->willReturn($expectedAuth);

        $result = $this->auth->handleAuth();

        $this->assertSame($expectedAuth, $result);
    }

    public function testCanClearAuth(): void
    {
        $this->authLibMock
            ->expects($this->once())
            ->method('clearAuthCookie');

        $this->auth->clearAuth();
    }
}

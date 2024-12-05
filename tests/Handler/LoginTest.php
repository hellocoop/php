<?php

namespace HelloCoop\Tests\Handler;

use HelloCoop\Handler\Login;
use HelloCoop\Config\HelloConfig;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Lib\Auth;
use HelloCoop\Lib\AuthHelper;
use HelloCoop\Lib\OIDCManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LoginTest extends TestCase
{
    private $configMock;
    private $authMock;
    private $helloRequestMock;
    private $oidcManagerMock;
    private $authHelperMock;
    private $login;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(HelloConfig::class);
        $this->authMock = $this->createMock(Auth::class);
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->oidcManagerMock = $this->createMock(OIDCManager::class);
        $this->authHelperMock = $this->createMock(AuthHelper::class);

        $this->login = new Login(
            $this->configMock,
            $this->authMock,
            $this->helloRequestMock,
            $this->oidcManagerMock,
            $this->authHelperMock,
            ['example.com' => 'https://example.com/callback']
        );
    }

    public function testGenerateLoginUrlSuccess()
    {
        // Setup mocks
        $this->configMock->method('getClientId')->willReturn('client_id');
        $this->configMock->method('getRedirectURI')->willReturn('https://example.com/callback');
        $this->helloRequestMock->method('fetchMultiple')->willReturn([
            'provider_hint' => 'google',
            'scope' => 'openid profile',
            'target_uri' => 'https://example.com/target',
            'redirect_uri' => 'https://example.com/callback',
            'nonce' => '1234',
            'prompt' => 'consent',
            'login_hint' => 'user@example.com',
            'domain_hint' => 'example.com'
        ]);
        $this->helloRequestMock->method('fetchHeader')->willReturn('example.com');

        // Mock the AuthHelper::createAuthRequest method
        $authResponse = [
            'url' => 'https://example.com/callback?client_id=client_id&redirect_uri=https://example.com/callback',
            'nonce' => '1234',
            'code_verifier' => 'code_verifier'
        ];

        $this->authHelperMock->method('createAuthRequest')->willReturn([
            'url' => 'https://example.com/callback?client_id=client_id&redirect_uri=https://example.com/callback',
            'nonce' => '1234',
            'code_verifier' => 'code_verifier',
        ]);

        // Test the URL generation
        $url = $this->login->generateLoginUrl();

        $this->assertEquals('https://example.com/callback?client_id=client_id&redirect_uri=https://example.com/callback', $url);
    }

    public function testGenerateLoginUrlMissingClientId()
    {
        $this->configMock->method('getClientId')->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing HELLO_CLIENT_ID configuration');

        $this->login->generateLoginUrl();
    }

    public function testGenerateLoginUrlMissingRedirectURI()
    {
        $this->configMock->method('getClientId')->willReturn('client_id');
        $this->configMock->method('getRedirectURI')->willReturn(null);
        $this->helloRequestMock->method('fetchMultiple')->willReturn([
            'provider_hint' => 'google',
            'scope' => 'openid profile',
            'target_uri' => 'https://example.com/target',
            'redirect_uri' => null
        ]);
        $this->helloRequestMock->method('fetchHeader')->willReturn('example2.com');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('RedirectURI not found');

        $this->login->generateLoginUrl();
    }

    private function mockStaticMethod($class, $method, $returnValue)
    {
        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods([$method])
            ->getMock();

        $mock->method($method)->willReturn($returnValue);
        $this->setUpMockedStaticMethod($class, $method, $mock);
    }

    private function setUpMockedStaticMethod($class, $method, $mock)
    {
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty('instances');
        $property->setAccessible(true);
        $property->setValue(null, null); // Clear previous instances to mock static methods

        $instance = $reflection->newInstanceWithoutConstructor();
        $methodRef = $reflection->getMethod($method);
        $methodRef->setAccessible(true);
        $methodRef->invoke($instance);
    }
}

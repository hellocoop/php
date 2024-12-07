<?php

namespace HelloCoop\Tests;

use HelloCoop\HelloClient;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\Crypto;
use HelloCoop\Renderers\PageRendererInterface;
use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;
use HelloCoop\Exception\CallbackException;

class HelloClientTest extends TestCase
{
    use PHPMock;

    private $helloRequestMock;
    private $helloResponseMock;
    private $configMock;
    private $pageRendererMock;
    private $client;

    private Crypto $crypto;

    protected function setUp(): void
    {
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->helloResponseMock = $this->createMock(HelloResponseInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->pageRendererMock = $this->createMock(PageRendererInterface::class);

        // Configure the ConfigInterface mock
        $this->configMock->method('getSecret')
            ->willReturn('1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef');
        $this->configMock->method('getCookies')
            ->willReturn([
                'authName' => 'authName',
                'oidcName' => 'oidcName',
            ]);
        $this->configMock->method('getClientId')
            ->willReturn('hello_php');
        $this->configMock->method('getRedirectURI')
            ->willReturn('/');

        // Mock the fetch() method of HelloRequestInterface
        $this->helloRequestMock->method('fetch')
            ->willReturnCallback(function ($key) {
                return $_GET[$key] ?? $_POST[$key] ?? null;
            });

        $this->helloRequestMock->method('getMethod')
            ->willReturnCallback(function () {
                return $_SERVER['REQUEST_METHOD'];
            });

        $this->helloRequestMock->method('getCookie')
            ->willReturnCallback(function ($key) {
                return $_COOKIE[$key] ?? null;
            });

        $this->crypto = new Crypto($this->configMock->getSecret());

        // Initialize HelloClient
        $this->client = new HelloClient(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->configMock,
            $this->pageRendererMock
        );

        $_COOKIE = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testRouteHandlesAuth()
    {
        // Simulate $_GET parameters
        $_GET = ['op' => 'auth'];
        $_COOKIE['oidcName'] = $this->crypto->encrypt([
            'code_verifier' => 'test_verifier',
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '/home',
        ]);

        $this->helloResponseMock
            ->expects($this->once())
            ->method('json')
            ->with($this->isType('array'))
            ->willReturn('auth_response');

        $result = $this->client->route();
        $this->assertSame('auth_response', $result);
    }

    public function testRouteHandlesCallback()
    {
        // Simulate $_GET parameters
        $_GET = ['code' => 'callback_code'];

        $_COOKIE['oidcName'] = $this->crypto->encrypt([
            'code_verifier' => 'test_verifier',
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '/home',
        ]);

        $this->helloResponseMock
            ->expects($this->once())
            ->method('redirect')
            ->with($this->isType('string'))
            ->willReturn('callback_response');

        $result = $this->client->route();
        $this->assertSame('callback_response', $result);
    }

    public function testRouteHandlesCallbackException()
    {
        // Simulate $_GET parameters
        $_GET = ['code' => 'callback_code'];

        $_COOKIE['oidcName'] = $this->crypto->encrypt([
            'code_verifier' => 'test_verifier',
            'nonce' => 'test_nonce',
            'redirect_uri' => 'https://example.com/callback',
            'target_uri' => '',
        ]);

        $this->pageRendererMock
            ->expects($this->once())
            ->method('renderErrorPage')
            ->with($this->isType('string'), $this->isType('string'), $this->isType('string'))
            ->willReturn('error_page');

        $this->helloResponseMock
            ->expects($this->once())
            ->method('render')
            ->with('error_page')
            ->willReturn('render_response');

        // Mock Callback handler to throw exception
        $callbackHandler = $this->getMockBuilder(\HelloCoop\Handler\Callback::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['handleCallback'])
            ->getMock();

        $callbackHandler
            ->method('handleCallback')
            ->willThrowException(new CallbackException(['error' => 'test', 'error_description' => 'desc', 'target_uri' => 'uri']));

        $result = $this->client->route();
        $this->assertSame('render_response', $result);
    }
}

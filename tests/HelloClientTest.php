<?php

namespace HelloCoop\Tests;

use HelloCoop\HelloClient;
use HelloCoop\Renderers\PageRendererInterface;
use PHPUnit\Framework\TestCase;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Tests\Traits\ServiceMocksTrait;
use PHPUnit\Framework\MockObject\MockObject;
use HelloCoop\Handler\Callback;

class HelloClientTest extends TestCase
{
    use ServiceMocksTrait;

    /** @var MockObject & PageRendererInterface */
    private $pageRendererMock;
    /** @var MockObject & Callback */
    private $callbackMock;
    private HelloClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpServiceMocks();
        $this->pageRendererMock = $this->createMock(PageRendererInterface::class);

        // Initialize HelloClient
        $this->client = new HelloClient(
            $this->configMock,
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->pageRendererMock
        );

        $this->callbackMock = $this->createMock(Callback::class);
        $this->replaceLazyLoadedProperty($this->client, 'callbackHandler', $this->callbackMock);

        $this->pageRendererMock->method('renderErrorPage')->willReturn("error_page");
    }

    public function testRouteHandlesAuth(): void
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
            ->with($this->isArray())
            ->willReturn('auth_response');

        $result = $this->client->route();
        $this->assertSame('auth_response', $result);
    }

    public function testRouteHandlesCallback(): void
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
            ->with('/dashboard')
            ->willReturn('/dashboard');

        $this->callbackMock
            ->method('handleCallback')
            ->willReturn('/dashboard');

        $result = $this->client->route();
        $this->assertSame('/dashboard', $result);
    }

    public function testRouteHandlesCallbackException(): void
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
            ->with($this->isString(), $this->isString(), $this->isString())
            ->willReturn('error_page');

        $this->helloResponseMock
            ->expects($this->once())
            ->method('render')
            ->with('error_page')
            ->willReturn('render_response');

        // Throw exception
        $this->callbackMock
            ->method('handleCallback')
            ->willThrowException(new CallbackException(['error' => 'test', 'error_description' => 'desc', 'target_uri' => 'uri']));

        $result = $this->client->route();
        $this->assertSame('render_response', $result);
    }

    public function testRouteHandlesWildcardConsole(): void
    {
        // Simulate $_GET parameters
        $_GET = ['wildcard_console' => 'true'];

        $_GET += [
            'uri' => 'https://example.com/wildcard',
            'target_uri' => '/target',
            'app_name' => 'TestApp',
            'redirect_uri' => '',
        ];

        $this->pageRendererMock
            ->expects($this->once())
            ->method('renderWildcardConsole')
            ->with(
                'https://example.com/wildcard',
                '/target',
                'TestApp',
                ''
            )
            ->willReturn('wildcard_console_page');

        $this->helloResponseMock
            ->expects($this->once())
            ->method('render')
            ->with('wildcard_console_page')
            ->willReturn('render_response');

        $result = $this->client->route();
        $this->assertSame('render_response', $result);
    }
}

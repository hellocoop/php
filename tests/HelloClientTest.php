<?php

namespace HelloCoop\Tests;

use HelloCoop\HelloClient;
use HelloCoop\Config\HelloConfig;
use HelloCoop\Handler\Auth;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Handler\Invite;
use HelloCoop\Handler\Logout;
use HelloCoop\Handler\Login;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Handler\Callback;
use HelloCoop\Exception\SameSiteCallbackException;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use PHPUnit\Framework\TestCase;

class HelloClientTest extends TestCase
{
    private $config;
    private $pageRenderer;
    private $callbackHandler;
    private $authHandler;
    private $invite;
    private $logout;
    private $login;
    private $helloRequest;
    private $helloResponse;
    private $helloClient;

    protected function setUp(): void
    {
        $this->config = $this->createMock(HelloConfig::class);
        $this->pageRenderer = $this->createMock(PageRendererInterface::class);
        $this->callbackHandler = $this->createMock(Callback::class);
        $this->authHandler = $this->createMock(Auth::class);
        $this->invite = $this->createMock(Invite::class);
        $this->logout = $this->createMock(Logout::class);
        $this->login = $this->createMock(Login::class);
        $this->helloRequest = $this->createMock(HelloRequestInterface::class);
        $this->helloResponse = $this->createMock(HelloResponseInterface::class);

        $this->helloClient = new HelloClient(
            $this->config,
            $this->pageRenderer,
            $this->callbackHandler,
            $this->authHandler,
            $this->invite,
            $this->logout,
            $this->login,
            $this->helloRequest,
            $this->helloResponse
        );
    }

    public function testRouteAuth()
    {
        $this->helloRequest->method('getMethod')->willReturn('GET');
        $this->helloRequest->method('fetch')->with('op')->willReturn('auth');

        $this->authHandler->expects($this->once())->method('handleAuth')->willReturn(AuthType::fromArray(['isLoggedIn' => true]));
        $this->helloResponse->expects($this->once())->method('json')->with([
            'isLoggedIn' => true,
            'cookieToken' => null,
            'authCookie' => null
        ]);

        $this->helloClient->route();
    }

    public function testRouteLogin()
    {
        $this->helloRequest->method('getMethod')->willReturn('GET');
        $this->helloRequest->method('fetch')->with('op')->willReturn('login');

        $this->login->expects($this->once())->method('generateLoginUrl')->willReturn('https://login.example.com');
        $this->helloResponse->expects($this->once())->method('redirect')->with('https://login.example.com');

        $this->helloClient->route();
    }

    public function testRouteCallback()
    {
        $this->helloRequest->method('getMethod')->willReturn('GET');
        $this->helloRequest->method('fetch')->withConsecutive(['op'], ['code'])->willReturnOnConsecutiveCalls(null, 'authCode');

        $this->callbackHandler->expects($this->once())->method('handleCallback')->willReturn('https://callback.example.com');
        $this->helloResponse->expects($this->once())->method('redirect')->with('https://callback.example.com');

        $this->helloClient->route();
    }

    public function testRouteSameSiteCallbackException()
    {
        $this->helloRequest->method('getMethod')->willReturn('GET');
        $this->helloRequest->method('fetch')->withConsecutive(['op'], ['code'])->willReturnOnConsecutiveCalls(null, 'authCode');

        $this->callbackHandler->method('handleCallback')->willThrowException(new SameSiteCallbackException());

        $this->pageRenderer->expects($this->once())->method('renderSameSitePage');
        $this->helloResponse->expects($this->once())->method('render');

        $this->helloClient->route();
    }

    public function testRouteInvalidMethod()
    {
        $this->helloRequest->method('getMethod')->willReturn('PUT');
        $this->helloResponse->expects($this->never())->method($this->anything());

        $this->helloClient->route();
    }
}

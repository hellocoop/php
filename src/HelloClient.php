<?php

namespace HelloCoop;

use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Handler\Auth;
use HelloCoop\Handler\Invite;
use HelloCoop\Handler\Logout;
use HelloCoop\Handler\Login;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Handler\Callback;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;

class HelloClient
{
    private ConfigInterface $config;
    private PageRendererInterface $pageRenderer;
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private Callback $callbackHandler;
    private Auth $authHandler;
    private Invite $invite;
    private Logout $logout;
    private Login $login;

    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        ConfigInterface $config,
        PageRendererInterface $pageRenderer
    ) {
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->config = $config;
        $this->pageRenderer = $pageRenderer;
    }

    private function getCallbackHandler(): Callback
    {
        return $this->callbackHandler ??= new Callback(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
        );
    }

    private function getAuthHandler(): Auth
    {
        return $this->authHandler ??= new Auth(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
        );
    }

    private function getInviteHandler(): Invite
    {
        return $this->invite ??= new Invite(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
        );
    }

    private function getLogoutHandler(): Logout
    {
        return $this->logout ??= new Logout(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
        );
    }

    private function getLoginHandler(): Login
    {
        return $this->login ??= new Login(
            $this->helloRequest,
            $this->helloResponse,
            $this->config,
            []
        );
    }

    public function getAuth(): array
    {
        return $this->getAuthHandler()->handleAuth()->toArray();
    }

    private function handleLogin()
    {
        return $this->helloResponse->redirect($this->getLoginHandler()->generateLoginUrl());
    }

    private function handleLogout()
    {
        return $this->helloResponse->redirect($this->getLogoutHandler()->generateLogoutUrl());
    }

    private function handleInvite()
    {
        return $this->helloResponse->redirect($this->getInviteHandler()->generateInviteUrl());
    }

    private function handleAuth(): string
    {
        $this->helloResponse->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
        $this->helloResponse->setHeader('Pragma', 'no-cache');
        $this->helloResponse->setHeader('Expires', '0');
        return $this->helloResponse->json($this->getAuthHandler()->handleAuth()->toArray());
    }

    private function handleCallback()
    {
        try {
            return $this->helloResponse->redirect($this->getCallbackHandler()->handleCallback());
        } catch (CallbackException $e) {
            $errorDetails = $e->getErrorDetails();
            return $this->helloResponse->render($this->pageRenderer->renderErrorPage(
                $errorDetails['error'],
                $errorDetails['error_description'],
                $errorDetails['target_uri']
            ));
        } catch (SameSiteCallbackException $e) {
            return $this->helloResponse->render($this->pageRenderer->renderSameSitePage());
        }
    }

    public function route()
    {
        if (in_array($this->helloRequest->getMethod(), ["POST", "GET"]) === false) {
            return;//TODO: add 500 error here;
        }

        $op = $this->helloRequest->fetch('op');
        if ($op) {
            switch ($op) {
                case 'auth':
                case 'getAuth':
                    return $this->handleAuth();
                case 'login':
                    return $this->handleLogin();
                case 'logout':
                    return $this->handleLogout();
                case 'invite':
                    return $this->handleInvite();
                default:
                    throw new \Exception('unknown query: ' . $op);
                    //TODO: add 500 error here;
            }
        }
        if ($this->helloRequest->fetch('code') || $this->helloRequest->fetch('error')) {
            return $this->handleCallback();
        }

        return; //TODO: add 500 error here;
    }
}

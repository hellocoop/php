<?php

namespace HelloCoop;

use HelloCoop\Config\ConfigInterface;
use HelloCoop\Handler\Auth;
use HelloCoop\Handler\Invite;
use HelloCoop\Handler\Logout;
use HelloCoop\Handler\Login;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Handler\Callback;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;

class HelloClient
{
    private ConfigInterface $config;
    private PageRendererInterface $pageRenderer;
    private Callback $callbackHandler;
    private Auth $authHandler;
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private Invite $invite;
    private Logout $logout;
    private Login $login;

    public function __construct(
        ConfigInterface $config,
        PageRendererInterface $pageRenderer,
        Callback $callbackHandler,
        Auth $authHandler,
        Invite $invite,
        Logout $logout,
        Login $login,
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse
    ) {
        $this->config = $config;
        $this->pageRenderer = $pageRenderer;

        $this->callbackHandler = $callbackHandler;
        $this->authHandler = $authHandler;
        $this->invite = $invite;
        $this->logout = $logout;
        $this->login = $login;

        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
    }
    public function getAuth(): array
    {
        return $this->authHandler->handleAuth()->toArray();
    }
    private function handleLogin()
    {
        return $this->helloResponse->redirect($this->login->generateLoginUrl());
    }
    private function handleLogout()
    {
        return $this->helloResponse->redirect($this->logout->generateLogoutUrl());
    }
    private function handleInvite()
    {
        return $this->helloResponse->redirect($this->invite->generateInviteUrl());
    }
    private function handleAuth(): string
    {
        $this->helloResponse->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
        $this->helloResponse->setHeader('Pragma', 'no-cache');
        $this->helloResponse->setHeader('Expires', '0');
        return $this->helloResponse->json($this->authHandler->handleAuth()->toArray());
    }
    private function handleCallback()
    {
        try {
            return $this->helloResponse->redirect($this->callbackHandler->handleCallback());
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
                    return; //TODO: add 500 error here;
            }
        }

        if ($this->helloRequest->fetch('code') || $this->helloRequest->fetch('error')) {
            return $this->handleCallback();
        }
        return; //TODO: add 500 error here;
    }
}

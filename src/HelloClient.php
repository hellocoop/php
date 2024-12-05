<?php

namespace HelloCoop;

use HelloCoop\Config\HelloConfig;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Handler\Callback;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;
use HelloCoop\Handler\Redirect\SimpleRedirector;
use HelloCoop\Handler\Auth;

class HelloClient
{
    private HelloConfig $config;
    private PageRendererInterface $pageRenderer;
    private Callback $callback;
    private SimpleRedirector $simpleRedirector;
    private Auth $authHandler;
    public function __construct(
        HelloConfig $config,
        PageRendererInterface $pageRenderer,
        Callback $callback,
        SimpleRedirector $simpleRedirector,
        Auth $authHandler
    ) {
        $this->config = $config;
        $this->pageRenderer = $pageRenderer;
        $this->callback = $callback;
        $this->simpleRedirector = $simpleRedirector;
        $this->authHandler = $authHandler;
    }
    public function getAuth(): array
    {
        $this->authHandler->handleAuth();
    }
    public function handleLogin()
    {
    }
    public function handleLogout()
    {
    }
    public function handleInvite()
    {
    }

    private function handleCallback(): void
    {
        try {
            $this->simpleRedirector->redirect($this->callback->handleCallback());
        } catch (CallbackException $e) {
            $errorDetails = $e->getErrorDetails();
            echo $this->pageRenderer->renderErrorPage(
                $errorDetails['error'],
                $errorDetails['error_description'],
                $errorDetails['target_uri']
            );
        } catch (SameSiteCallbackException $e) {
            echo $this->pageRenderer->renderSameSitePage();
        }
    }
}

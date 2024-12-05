<?php

namespace HelloCoop;

use HelloCoop\Config\HelloConfig;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Handler\Callback;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;
use HelloCoop\Handler\Redirect\SimpleRedirector;

class HelloClient
{
    private HelloConfig $config;
    private PageRendererInterface $pageRenderer;
    private Callback $callback;
    private SimpleRedirector $simpleRedirector;
    public function __construct(
        HelloConfig $config,
        PageRendererInterface $pageRenderer,
        Callback $callback,
        SimpleRedirector $simpleRedirector
    ) {
        $this->config = $config;
        $this->pageRenderer = $pageRenderer;
        $this->callback = $callback;
        $this->simpleRedirector = $simpleRedirector;
    }
    public function getAuth(): array
    {
        return ['isLoggedIn' => false];
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

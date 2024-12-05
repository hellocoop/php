<?php

namespace HelloCoop;

use HelloCoop\Config\HelloConfig;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Handler\Callback;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Handler\Auth;

class HelloClient
{
    private HelloConfig $config;
    private PageRendererInterface $pageRenderer;
    private Callback $callbackHandler;
    private Auth $authHandler;

    private HelloResponseInterface $helloResponse;

    public function __construct(
        HelloConfig $config,
        PageRendererInterface $pageRenderer,
        Callback $callbackHandler,
        Auth $authHandler,
        HelloResponseInterface $helloResponse
    ) {
        $this->config = $config;
        $this->pageRenderer = $pageRenderer;
        $this->callbackHandler = $callbackHandler;
        $this->authHandler = $authHandler;
        $this->helloResponse = $helloResponse;
    }
    public function getAuth(): array
    {
        return $this->authHandler->handleAuth()->toArray();
    }
    private function handleLogin()
    {
    }
    private function handleLogout()
    {
    }
    private function handleInvite()
    {
    }
    private function handleAuth(): void
    {
        $this->helloResponse->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
        $this->helloResponse->setHeader('Pragma', 'no-cache');
        $this->helloResponse->setHeader('Expires', '0');
        echo json_encode($this->authHandler->handleAuth()->toArray());
    }
    private function handleCallback(): void
    {
        try {
            $this->helloResponse->redirect($this->callbackHandler->handleCallback());
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

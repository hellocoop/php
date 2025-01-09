<?php

namespace HelloCoop;

use Exception;
use HelloCoop\Exception\CryptoFailedException;
use HelloCoop\Exception\InvalidSecretException;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloRequest\HelloRequest;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloResponse\HelloResponse;
use HelloCoop\Renderers\PageRendererInterface;
use HelloCoop\Renderers\DefaultPageRenderer;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Handler\Auth;
use HelloCoop\Handler\Invite;
use HelloCoop\Handler\Logout;
use HelloCoop\Handler\Login;
use HelloCoop\Handler\Callback;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;

class HelloClient
{
    private ConfigInterface $config;
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private PageRendererInterface $pageRenderer;
    private ?Callback $callbackHandler = null;
    private ?Auth $authHandler = null;
    private ?Invite $invite = null;
    private ?Logout $logout = null;
    private ?Login $login = null;

    public function __construct(
        ConfigInterface $config,
        ?HelloRequestInterface $helloRequest = null,
        ?HelloResponseInterface $helloResponse = null,
        ?PageRendererInterface $pageRenderer = null
    ) {
        $this->config = $config;
        $this->helloRequest = $helloRequest ??= new HelloRequest();
        $this->helloResponse = $helloResponse  ??= new HelloResponse();
        $this->pageRenderer = $pageRenderer ??= new DefaultPageRenderer();
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

    /**
     * @return array<string, bool|string|array<string, mixed>|null>
     */
    public function getAuth(): array
    {
        return$this->getAuthHandler()->handleAuth()->toArray();
    }

    /**
     * @return mixed|null
     * @throws CryptoFailedException | InvalidSecretException
     */
    private function handleLogin()
    {
        return $this->helloResponse->redirect($this->getLoginHandler()->generateLoginUrl());
    }

    /**
     * @return mixed|null
     */
    private function handleLogout()
    {
        return $this->helloResponse->redirect($this->getLogoutHandler()->generateLogoutUrl());
    }

    /**
     * @return mixed|null
     * @throws Exception
     */
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

    /**
     * @return mixed|string|null
     */
    private function handleCallback()
    {
        try {
            return $this->helloResponse->redirect($this->getCallbackHandler()->handleCallback());
        } catch (CallbackException $e) {
            $errorDetails = $e->getErrorDetails();
            /** @var string $error */
            $error =  $errorDetails['error'];
            /** @var string $errorDescription */
            $errorDescription =  $errorDetails['error_description'];
            /** @var string $targetUri */
            $targetUri =  $errorDetails['target_uri'];
            return $this->helloResponse->render($this->pageRenderer->renderErrorPage(
                $error,
                $errorDescription,
                $targetUri
            ));
        } catch (SameSiteCallbackException $e) {
            return $this->helloResponse->render($this->pageRenderer->renderSameSitePage());
        }
    }

    /**
     * @return mixed|string|void|null
     * @throws CryptoFailedException | InvalidSecretException | Exception
     */
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
                    throw new Exception('unknown query: ' . $op);
                    //TODO: add 500 error here;
            }
        }
        if ($this->helloRequest->fetch('code') || $this->helloRequest->fetch('error')) {
            return $this->handleCallback();
        }
        // If the Redirect URI is not configured in Hello Wallet, we will prompt the user to add it.
        if (
            $this->helloRequest->fetch('wildcard_console') &&
            empty($this->helloRequest->fetch('redirect_uri'))
        ) {
            return $this->helloResponse->render($this->pageRenderer->renderWildcardConsole(
                (string)$this->helloRequest->fetch('uri'),
                (string)$this->helloRequest->fetch('target_uri'),
                (string)$this->helloRequest->fetch('app_name'),
                (string)$this->helloRequest->fetch('redirect_uri')
            ));
        }

        return; //TODO: add 500 error here;
    }
}

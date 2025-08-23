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
use HelloCoop\Handler\Command;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;

final class HelloClient
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
    private ?Command $commandHandler = null;
    private string $issuer;

    public function __construct(
        ConfigInterface $config,
        ?HelloRequestInterface $helloRequest = null,
        ?HelloResponseInterface $helloResponse = null,
        ?PageRendererInterface $pageRenderer = null
    ) {
        $this->config        = $config;
        $this->helloRequest  = $helloRequest  ?? new HelloRequest();
        $this->helloResponse = $helloResponse ?? new HelloResponse();
        $this->pageRenderer  = $pageRenderer  ?? new DefaultPageRenderer();
        $this->issuer        = 'https://issuer.' . (string)$this->config->getHelloDomain();
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

    private function getCommandHandler(): Command
    {
        return $this->commandHandler ??= new Command(
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
     * Return a flattened auth summary.
     *
     * @return array<string, bool|string|null>
     */
    public function getAuth(): array
    {
        $auth = $this->getAuthHandler()->handleAuth()->toArray();

        $isLoggedIn = isset($auth['isLoggedIn']) ? (bool)$auth['isLoggedIn'] : false;

        if ($isLoggedIn) {
            $cookie = $auth['authCookie'] ?? null;
            if (is_array($cookie)) {
                return [
                    'isLoggedIn'     => true,
                    'email'          => isset($cookie['email']) ? (string)$cookie['email'] : null,
                    'email_verified' => isset($cookie['email_verified']) ? (bool)$cookie['email_verified'] : null,
                    'name'           => isset($cookie['name']) ? (string)$cookie['name'] : null,
                    'picture'        => isset($cookie['picture']) ? (string)$cookie['picture'] : null,
                    'sub'            => isset($cookie['sub']) ? (string)$cookie['sub'] : null,
                ];
            }
        }

        return ['isLoggedIn' => $isLoggedIn];
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
    private function handleCommand()
    {
        $this->helloResponse->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
        $this->helloResponse->setHeader('Pragma', 'no-cache');
        $this->helloResponse->setHeader('Expires', '0');
        return $this->getCommandHandler()->handleCommand();
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

        // json() expects array<string, mixed>
        /** @var array<string, mixed> $payload */
        $payload = $this->getAuth();

        return $this->helloResponse->json($payload);
    }

    /**
     * @return mixed|string|null
     */
    private function handleCallback()
    {
        try {
            return $this->helloResponse->redirect($this->getCallbackHandler()->handleCallback());
        } catch (CallbackException $e) {
            /** @var array<string,string> $errorDetails */
            $errorDetails = $e->getErrorDetails();
            $error            = (string)($errorDetails['error'] ?? 'callback_error');
            $errorDescription = (string)($errorDetails['error_description'] ?? 'An error occurred');
            $targetUri        = (string)($errorDetails['target_uri'] ?? '/');

            return $this->helloResponse->render(
                $this->pageRenderer->renderErrorPage($error, $errorDescription, $targetUri)
            );
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
        $method = $this->helloRequest->getMethod();
        if (!in_array($method, ['POST', 'GET'], true)) {
            return; // TODO: add 500 error here
        }

        if ($method === 'POST' && $this->helloRequest->has('command_token')) {
            return $this->handleCommand();
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
                    throw new Exception('unknown query: ' . (string)$op);
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
                ''
            ));
        }

        if (
            $this->helloRequest->fetch('iss') ||
            $this->helloRequest->fetch('login_hint') ||
            $this->helloRequest->fetch('domain_hint') ||
            $this->helloRequest->fetch('target_link_uri') ||
            $this->helloRequest->fetch('redirect_uri')
        ) {
            $iss = $this->helloRequest->fetch('iss');
            $issStr = $iss !== null ? (string)$iss : '';

            if ($issStr !== '' && $issStr !== $this->issuer) {
                // Make this associative so it matches array<string, mixed>
                return $this->helloResponse->json([
                    'error' => sprintf("Passed iss '%s' must be '%s'", $issStr, $this->issuer),
                ]);
            }

            return $this->helloResponse->redirect($this->getLoginHandler()->generateLoginUrl());
        }

        return; // TODO: add 500 error here
    }
}

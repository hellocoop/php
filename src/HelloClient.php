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

        $domainRaw = $this->config->getHelloDomain();
        $domain    = is_string($domainRaw) ? $domainRaw : '';
        $this->issuer = 'https://issuer.' . $domain;
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
                    'email'          => isset($cookie['email']) && is_string($cookie['email']) ? $cookie['email'] : null,
                    'email_verified' => isset($cookie['email_verified']) ? (bool)$cookie['email_verified'] : null,
                    'name'           => isset($cookie['name']) && is_string($cookie['name']) ? $cookie['name'] : null,
                    'picture'        => isset($cookie['picture']) && is_string($cookie['picture']) ? $cookie['picture'] : null,
                    'sub'            => isset($cookie['sub']) && is_string($cookie['sub']) ? $cookie['sub'] : null,
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
            $details = $e->getErrorDetails();
            $error            = is_array($details) && isset($details['error']) && is_string($details['error']) ? $details['error'] : 'callback_error';
            $errorDescription = is_array($details) && isset($details['error_description']) && is_string($details['error_description']) ? $details['error_description'] : 'An error occurred';
            $targetUri        = is_array($details) && isset($details['target_uri']) && is_string($details['target_uri']) ? $details['target_uri'] : '/';

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

        $opRaw = $this->helloRequest->fetch('op');
        $op    = is_string($opRaw) ? $opRaw : null;

        if ($op !== null) {
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
            }
        }

        $hasCode  = $this->helloRequest->fetch('code');
        $hasError = $this->helloRequest->fetch('error');
        if ($hasCode !== null || $hasError !== null) {
            return $this->handleCallback();
        }

        // If the Redirect URI is not configured in Hello Wallet, we will prompt the user to add it.
        $wildcardConsole = $this->helloRequest->fetch('wildcard_console');
        $redirectUriRaw  = $this->helloRequest->fetch('redirect_uri');
        $redirectUriStr  = is_string($redirectUriRaw) ? $redirectUriRaw : '';

        if ($wildcardConsole !== null && $redirectUriStr === '') {
            $uri        = $this->helloRequest->fetch('uri');
            $targetUri  = $this->helloRequest->fetch('target_uri');
            $appName    = $this->helloRequest->fetch('app_name');

            $uriStr       = is_string($uri) ? $uri : '';
            $targetUriStr = is_string($targetUri) ? $targetUri : '';
            $appNameStr   = is_string($appName) ? $appName : '';

            return $this->helloResponse->render($this->pageRenderer->renderWildcardConsole(
                $uriStr,
                $targetUriStr,
                $appNameStr,
                ''
            ));
        }

        $issRaw           = $this->helloRequest->fetch('iss');
        $loginHintRaw     = $this->helloRequest->fetch('login_hint');
        $domainHintRaw    = $this->helloRequest->fetch('domain_hint');
        $targetLinkUriRaw = $this->helloRequest->fetch('target_link_uri');

        if (
            $issRaw !== null ||
            $loginHintRaw !== null ||
            $domainHintRaw !== null ||
            $targetLinkUriRaw !== null ||
            $redirectUriStr !== ''
        ) {
            $issStr = is_string($issRaw) ? $issRaw : '';

            if ($issStr !== '' && $issStr !== $this->issuer) {
                return $this->helloResponse->json([
                    'error' => sprintf("Passed iss '%s' must be '%s'", $issStr, $this->issuer),
                ]);
            }

            return $this->helloResponse->redirect($this->getLoginHandler()->generateLoginUrl());
        }

        return; // TODO: add 500 error here
    }
}

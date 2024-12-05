<?php

namespace HelloCoop\Handler;

use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\HelloConfig;
use HelloCoop\Config\Constants;
use HelloCoop\Lib\OIDCManager;
use HelloCoop\Lib\Auth;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Exception\SameSiteCallbackException;
use HelloCoop\Lib\TokenFetcher;
use HelloCoop\Lib\TokenParser;
use Exception;

class Callback
{
    private HelloRequestInterface $helloRequest;
    private HelloConfig $config;
    private OIDCManager $oidcManager;
    private Auth $auth;
    private TokenFetcher $tokenFetcher;

    private TokenParser $tokenParser;

    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloConfig $config,
        OIDCManager $oidcManager,
        Auth $auth,
        TokenFetcher $tokenFetcher,
        TokenParser $tokenParser
    ) {
        $this->helloRequest = $helloRequest;
        $this->config = $config;
        $this->oidcManager = $oidcManager;
        $this->auth = $auth;
        $this->tokenFetcher = $tokenFetcher;
        $this->tokenParser = $tokenParser;
    }

    public function handleCallback(): ?string
    {
        try {
            $params = $this->helloRequest->fetchMultiple([
                'code',
                'error',
                'same_site',
                'wildcard_domain',
                'app_name',
                'redirect_uri',
                'nonce'
            ]);

            $code = $params['code'] ?? null;
            $error = $params['error'] ?? null;
            $sameSite = $params['same_site'] ?? null;
            $wildcardDomain = $params['wildcard_domain'] ?? null;
            $appName = $params['app_name'] ?? null;
            $redirectUri = $params['redirect_uri'] ?? null;
            $nonce = $params['nonce'] ?? null;

            if ($this->config->getSameSiteStrict() && !$sameSite) {
                throw new SameSiteCallbackException();
            }

            $oidcState = $this->oidcManager->getOidc()->toArray();

            if (!$oidcState) {
                return $this->sendErrorPage([
                    'error' => 'invalid_request',
                    'error_description' => 'OpenID Connect cookie lost',
                    'target_uri' => '',
                ], 'OpenID Connect cookie lost during callback handling.');
            }

            $codeVerifier = $oidcState['code_verifier'] ?? null;
            $targetUri = $oidcState['target_uri'] ?? null;

            if ($error) {
                return $this->sendErrorPage($params, 'Callback contains an error.');
            }

            if (!$code) {
                return $this->sendErrorPage([
                    'error' => 'invalid_request',
                    'error_description' => 'Missing code parameter',
                    'target_uri' => $targetUri,
                ], 'Missing code parameter in callback request.');
            }

            if (is_array($code)) {
                return $this->sendErrorPage([
                    'error' => 'invalid_request',
                    'error_description' => 'Received more than one code',
                    'target_uri' => $targetUri,
                ], 'Received multiple codes in callback request.');
            }

            if (!$codeVerifier) {
                return $this->sendErrorPage([
                    'error' => 'invalid_request',
                    'error_description' => 'Missing code_verifier from session',
                    'target_uri' => $targetUri,
                ], 'Missing code_verifier in callback request.');
            }

            $this->oidcManager->clearOidcCookie();
            $token = $this->tokenFetcher->fetchToken([
                'code' => (string) $code,
                'wallet' => $this->config->getHelloWallet(),
                'code_verifier' => $codeVerifier,
                'redirect_uri' => $redirectUri,
                'client_id' => $this->config->getClientId()
            ]);

            $payload = $this->tokenParser->parseToken($token)['payload'];
            if ($payload['aud'] != $this->config->getClientId()) {
                return $this->sendErrorPage([
                    'error' => 'invalid_client',
                    'error_description' => 'Wrong ID token audience',
                    'target_uri' => $targetUri,
                ], 'Wrong ID token audience.');
            }

            if ($payload['nonce'] != $nonce) {
                return $this->sendErrorPage([
                    'error' => 'invalid_request',
                    'error_description' => 'Wrong nonce in ID token',
                    'target_uri' => $targetUri,
                ], 'Wrong nonce in ID token.');
            }

            $currentTimeInt = time();
            if ($payload['exp'] < $currentTimeInt) {
                return $this->sendErrorPage([
                    'error' => 'invalid_request',
                    'error_description' => 'The ID token has expired.',
                    'target_uri' => $targetUri,
                ], 'ID token expired.');
            }

            if ($payload['iat'] > $currentTimeInt + 5) { // 5 seconds clock skew
                return $this->sendErrorPage([
                    'error' => 'invalid_request',
                    'error_description' => 'The ID token is not yet valid.',
                    'target_uri' => $targetUri,
                ], 'ID token is not yet valid.');
            }

            $auth = [
                'isLoggedIn' => true,
                'sub' => $payload['sub'],
                'iat' => $payload['iat']
            ];

            $validClaims = Constants::getValidIdentityClaims();
            foreach ($validClaims as $claim) {
                if (isset($payload[$claim])) {
                    $auth[$claim] = $payload[$claim];
                }
            }

            if ($auth['isLoggedIn'] && isset($payload['org'])) {
                $auth['org'] = $payload['org'];
            }

            if ($this->config->getLoginSync()) {
                try {
                    $callback = call_user_func($this->config->getLoginSync(), [
                        'token' => $token,
                        'payload' => $payload,
                        'target_uri' => $targetUri
                    ]);

                    $targetUri = $callback['target_uri'] ?? $targetUri;
                    if ($callback['accessDenied']) {
                        return $this->sendErrorPage([
                            'error' => 'access_denied',
                            'error_description' => 'loginSync denied access',
                            'target_uri' => $targetUri,
                        ], 'Access denied by loginSync.');
                    } elseif ($callback['updatedAuth']) {
                        $auth = array_merge($callback['updatedAuth'], $auth);
                    }
                } catch (Exception $e) {
                    return $this->sendErrorPage([
                        'error' => 'server_error',
                        'error_description' => 'loginSync failed',
                        'target_uri' => $targetUri,
                    ], 'loginSync failed.', $e);
                }
            }

            if ($wildcardDomain) {
                // the redirect_uri is not registered at Hellō - prompt to add
                $appName = is_array($appName) ? $appName[0] : $appName;
                $appName = $appName ?: 'Your App'; // Default to 'Your App' if $appName is empty

                $queryParams = [
                    'uri' => is_array($wildcardDomain) ? $wildcardDomain[0] : $wildcardDomain,
                    'appName' => $appName,
                    'redirectURI' => $redirectUri,
                    'targetURI' => $targetUri,
                    'wildcard_console' => 'true',
                ];

                // Build query string
                $queryString = http_build_query($queryParams);

                // Update targetUri with the apiRoute and query string
                $targetUri = $this->config->getApiRoute() . '?' . $queryString;
            }

            $targetUri = $targetUri ?: $this->config->getRoutes()['loggedIn'] ?: '/';
            $this->auth->saveAuthCookie(AuthType::fromArray($auth));
            return $targetUri;
        } catch (Exception $e) {
            if (!($e instanceof SameSiteCallbackException) && !($e instanceof CallbackException)) {
                $this->oidcManager->clearOidcCookie();
            }
            // Let it handled in HelloClient
            throw $e;
        }
    }

    /**
     * Constructs and returns a URL for the error page with updated query parameters.
     *
     * Uses the target URI from error details or a fallback error route. Updates the query
     * string with error information. Throws an exception if no error URI is available.
     *
     * @param array $error Error details including 'target_uri', 'error', and 'error_description'.
     * @param string $errorMessage A message describing the error.
     * @param \Throwable|null $previous Previous exception for chaining (optional).
     *
     * @return string The error page URL.
     *
     * @throws CallbackException If no error URI is provided.
     */
    private function sendErrorPage(array $error, string $errorMessage, \Throwable $previous = null): string
    {
        $error_uri = $error['target_uri'] ?? $this->config->getRoutes()['error'] ?? null;
        if ($error_uri) {
            list($pathString, $queryString) = array_pad(explode('?', $error_uri, 2), 2, '');
            // Parse the query string into an array
            parse_str($queryString, $queryArray);
            foreach ($error as $key => $value) {
                if (strpos($key, 'error') === 0) {
                    $queryArray[$key] = $value;
                }
            }
            // Build the new query string
            $newQueryString = http_build_query($queryArray);
            // Construct the URL
            return $pathString . '?' . $newQueryString;
        }
        throw new CallbackException($error, $errorMessage, 0, $previous);
    }
}

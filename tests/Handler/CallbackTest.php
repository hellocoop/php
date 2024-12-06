<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Callback;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\OIDCManager;
use HelloCoop\Lib\Auth;
use HelloCoop\Lib\TokenFetcher;
use HelloCoop\Lib\TokenParser;
use HelloCoop\Exception\CallbackException;
use HelloCoop\Type\OIDC;

class CallbackTest extends TestCase
{
    private $helloRequestMock;
    private $configMock;
    private $oidcManagerMock;
    private $authMock;
    private $tokenFetcherMock;

    private $tokenParserMock;
    private $callback;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->oidcManagerMock = $this->createMock(OIDCManager::class);
        $this->authMock = $this->createMock(Auth::class);
        $this->tokenFetcherMock = $this->createMock(TokenFetcher::class);
        $this->tokenParserMock = $this->createMock(TokenParser::class);

        // Create instance of Callback
        $this->callback = new Callback(
            $this->helloRequestMock,
            $this->configMock,
            $this->oidcManagerMock,
            $this->authMock,
            $this->tokenFetcherMock,
            $this->tokenParserMock
        );

        // $this->oidcManagerMock->method('getOidc')->willReturn(OIDC::fromArray([
        //     'code_verifier' => 'valid_code_verifier',
        //     'target_uri' => '/dashboard',
        //     'nonce' => '',
        //     'redirect_uri' => '/'
        // ]));
    }

    public function testHandleCallbackSuccessfulLogin()
    {
        // Set up mock behaviors
        $params = [
            'code' => 'valid_code',
            'error' => null,
            'same_site' => 'Strict',
            'wildcard_domain' => 'example.com',
            'app_name' => 'MyApp',
            'redirect_uri' => 'http://redirect.com',
            'nonce' => 'valid_nonce'
        ];

        $this->helloRequestMock->method('fetchMultiple')->willReturn($params);

        $this->configMock->method('getSameSiteStrict')->willReturn(true);
        $this->configMock->method('getHelloWallet')->willReturn('valid_wallet');
        $this->configMock->method('getClientId')->willReturn('valid_client_id');
        $this->configMock->method('getApiRoute')->willReturn('http://api.example.com');
        $this->configMock->method('getRoutes')->willReturn(['loggedIn' => '/home']);

        $this->oidcManagerMock->expects(self::once())->method('clearOidcCookie');

        $this->authMock->method('saveAuthCookie')->willReturn(true);

        $this->oidcManagerMock->method('getOidc')->willReturn(OIDC::fromArray([
            'code_verifier' => 'valid_code_verifier',
            'target_uri' => '/dashboard',
            'nonce' => '',
            'redirect_uri' => '/'
        ]));

        $this->tokenFetcherMock->method('fetchToken')->willReturn('valid_id_token');
        $this->tokenParserMock->method('parseToken')->willReturn([
            'payload' => [
                'aud' => 'valid_client_id',
                'nonce' => 'valid_nonce',
                'sub' => 'user_sub',
                'iat' => time(),
                'exp' => time() + 3600
            ]
        ]);

        // Call the method under test
        $result = $this->callback->handleCallback();

        // Assert that the result is the expected target URI
        $this->assertEquals('http://api.example.com?uri=example.com&appName=MyApp&redirectURI=http%3A%2F%2Fredirect.com&targetURI=%2Fdashboard&wildcard_console=true', $result);
    }

    public function testHandleCallbackMissingCode()
    {
        // Set up mock behaviors
        $params = [
            'code' => null,  // Missing code
            'error' => null,
            'same_site' => 'Strict',
            'wildcard_domain' => 'example.com',
            'app_name' => 'MyApp',
            'redirect_uri' => 'http://redirect.com',
            'nonce' => 'valid_nonce'
        ];

        $this->oidcManagerMock->method('getOidc')->willReturn(OIDC::fromArray([
                'code_verifier' => 'valid_code_verifier',
                'target_uri' => '/dashboard',
                'nonce' => '',
                'redirect_uri' => '/'
        ]));

        $this->helloRequestMock->method('fetchMultiple')->willReturn($params);

        $this->assertEquals('/dashboard?error=invalid_request&error_description=Missing+code+parameter', $this->callback->handleCallback());
    }

    public function testHandleCallbackInvalidTokenAudience()
    {
        // Set up mock behaviors
        $params = [
            'code' => 'valid_code',
            'error' => null,
            'same_site' => 'Strict',
            'wildcard_domain' => 'example.com',
            'app_name' => 'MyApp',
            'redirect_uri' => 'http://redirect.com',
            'nonce' => 'valid_nonce'
        ];

        $this->oidcManagerMock->method('getOidc')->willReturn(OIDC::fromArray([
            'code_verifier' => 'valid_code_verifier',
            'target_uri' => '',
            'nonce' => '',
            'redirect_uri' => '/'
        ]));

        $this->helloRequestMock->method('fetchMultiple')->willReturn($params);

        $this->configMock->method('getSameSiteStrict')->willReturn(true);
        $this->configMock->method('getHelloWallet')->willReturn('valid_wallet');
        $this->configMock->method('getClientId')->willReturn('valid_client_id');
        $this->configMock->method('getApiRoute')->willReturn('http://api.example.com');
        $this->configMock->method('getRoutes')->willReturn(['loggedIn' => '/home']);

        $this->oidcManagerMock->expects(self::once())->method('clearOidcCookie');

        $this->tokenFetcherMock->method('fetchToken')->willReturn('valid_id_token');

        $this->tokenParserMock->method('parseToken')->willReturn([
            'payload' => [
                'aud' => 'valid_client_00',
                'nonce' => 'valid_nonce',
                'sub' => 'user_sub',
                'iat' => time(),
                'exp' => time() + 3600
            ]
        ]);

        $this->authMock->method('saveAuthCookie')->willReturn(true);

        // Expect CallbackException for invalid audience
        $this->expectException(CallbackException::class);
        $this->expectExceptionMessage('Wrong ID token audience.');

        // Call the method under test
        $this->callback->handleCallback();
    }
}

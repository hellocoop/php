<?php

declare(strict_types=1);

namespace HelloCoop\Handler;

use Exception;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\Auth as AuthLib;

final class Invite
{
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private ConfigInterface $config;
    private ?AuthLib $authLib = null;

    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        ConfigInterface $config
    ) {
        $this->helloRequest  = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->config        = $config;
    }

    private function getAuthLib(): AuthLib
    {
        return $this->authLib ??= new AuthLib(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
        );
    }

    /**
     * Build origin like "https://example.com[:port]" from a URL.
     * Falls back to empty string if not derivable.
     */
    private function buildOrigin(?string $url): string
    {
        if ($url === null || $url === '') {
            return '';
        }
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return '';
        }
        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) {
            $origin .= ':' . (string)$parts['port'];
        }
        return $origin;
    }

    /**
     * @throws Exception
     */
    public function generateInviteUrl(): string
    {
        $params = $this->helloRequest->fetchMultiple([
            'target_uri',
            'app_name',
            'prompt',
            'role',
            'tenant',
            'state',
            'redirect_uri',
        ]);

        $auth    = $this->getAuthLib()->getAuthfromCookies();
        $authArr = $auth->toArray();
        /** @var array<string,mixed>|null $cookie */
        $cookie  = isset($authArr['authCookie']) && is_array($authArr['authCookie'])
            ? $authArr['authCookie']
            : null;

        if ($cookie === null) {
            throw new Exception('User not logged in');
        }
        $inviterSub = isset($cookie['sub']) ? (string)$cookie['sub'] : '';
        if ($inviterSub === '') {
            throw new Exception('User cookie missing');
        }

        // Choose redirect URI: request param overrides config if present
        $redirectURIParam = isset($params['redirect_uri']) ? (string)$params['redirect_uri'] : null;
        $redirectURIConf  = $this->config->getRedirectURI(); // ?string
        $redirectURI      = $redirectURIParam !== null && $redirectURIParam !== ''
            ? $redirectURIParam
            : ($redirectURIConf ?? '');

        $origin = $this->buildOrigin($redirectURI);
        $defaultTargetURI = ($origin !== '' ? $origin . '/' : '/');

        // Ensure strings for sprintf()
        $inviterName = isset($cookie['name']) && is_string($cookie['name'])
            ? $cookie['name']
            : (isset($authArr['name']) && is_string($authArr['name']) ? $authArr['name'] : 'Someone');

        $appName = isset($params['app_name']) ? (string)$params['app_name'] : 'your app';

        $defaultPrompt = sprintf(
            '%s has invited you to join %s',
            (string)$inviterName,
            (string)$appName
        );

        $request = [
            'app_name'           => isset($params['app_name']) ? (string)$params['app_name'] : null,
            'prompt'             => isset($params['prompt']) ? (string)$params['prompt'] : $defaultPrompt,
            'role'               => isset($params['role']) ? (string)$params['role'] : null,
            'tenant'             => isset($params['tenant']) ? (string)$params['tenant'] : null,
            'state'              => isset($params['state']) ? (string)$params['state'] : null,
            'inviter'            => $inviterSub,
            'client_id'          => (string)$this->config->getClientId(),
            'initiate_login_uri' => $redirectURI !== '' ? $redirectURI : '/',
            'return_uri'         => isset($params['target_uri']) && $params['target_uri'] !== ''
                ? (string)$params['target_uri']
                : $defaultTargetURI,
        ];

        // Remove nulls so http_build_query only serializes present fields
        $request = array_filter(
            $request,
            static fn($v) => $v !== null
        );

        $queryString = http_build_query($request, '', '&', PHP_QUERY_RFC3986);

        return 'https://wallet.' . (string)$this->config->getHelloDomain() . '/invite?' . $queryString;
    }
}

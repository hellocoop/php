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
     * Safely fetch a string from an array of mixed.
     * @param array<string,mixed> $arr
     */
    private function strFrom(array $arr, string $key, ?string $default = null): ?string
    {
        $v = $arr[$key] ?? null;
        return is_string($v) ? $v : $default;
    }

    /**
     * @throws Exception
     */
    public function generateInviteUrl(): string
    {
        /** @var array<string,mixed> $params */
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
        $cookie = isset($authArr['authCookie']) && is_array($authArr['authCookie'])
            ? $authArr['authCookie']
            : null;

        if ($cookie === null) {
            throw new Exception('User not logged in');
        }

        $inviterSub = $this->strFrom($cookie, 'sub', '');
        if ($inviterSub === '') {
            throw new Exception('User cookie missing');
        }

        // Choose redirect URI: request param overrides config if present
        $redirectURIParam = $this->strFrom($params, 'redirect_uri');
        $redirectURIConf  = $this->config->getRedirectURI(); // ?string
        $redirectURI      = $redirectURIParam !== null && $redirectURIParam !== ''
            ? $redirectURIParam
            : ($redirectURIConf ?? '');

        $origin = $this->buildOrigin($redirectURI);
        $defaultTargetURI = ($origin !== '' ? $origin . '/' : '/');

        // Safe inviter/app names
        $inviterName = $this->strFrom($cookie, 'name')
            ?? $this->strFrom($authArr, 'name')
            ?? 'Someone';

        $appName = $this->strFrom($params, 'app_name', 'your app');

        $defaultPrompt = sprintf(
            '%s has invited you to join %s',
            $inviterName,
            $appName
        );

        // Safe scalar config values
        $clientIdRaw    = $this->config->getClientId();
        $clientId       = is_string($clientIdRaw) ? $clientIdRaw : '';
        $helloDomainRaw = $this->config->getHelloDomain();
        $helloDomain    = is_string($helloDomainRaw) ? $helloDomainRaw : '';

        // Normalize return URI first to avoid ternary that PHPStan flags
        $targetUri  = $this->strFrom($params, 'target_uri');
        $returnUri  = ($targetUri === null || $targetUri === '') ? $defaultTargetURI : $targetUri;

        $request = [
            'app_name'           => $this->strFrom($params, 'app_name'),
            'prompt'             => $this->strFrom($params, 'prompt', $defaultPrompt),
            'role'               => $this->strFrom($params, 'role'),
            'tenant'             => $this->strFrom($params, 'tenant'),
            'state'              => $this->strFrom($params, 'state'),
            'inviter'            => $inviterSub,
            'client_id'          => $clientId,
            'initiate_login_uri' => $redirectURI !== '' ? $redirectURI : '/',
            'return_uri'         => $returnUri,
        ];

        // Remove nulls so http_build_query only serializes present fields
        $request = array_filter(
            $request,
            static fn($v) => $v !== null
        );

        $queryString = http_build_query($request, '', '&', PHP_QUERY_RFC3986);

        return 'https://wallet.' . $helloDomain . '/invite?' . $queryString;
    }
}

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

        $auth     = $this->getAuthLib()->getAuthfromCookies();
        $authArr  = $auth->toArray();
        $cookie   = $authArr['authCookie'] ?? null;

        if (empty($cookie)) {
            throw new Exception('User not logged in');
        }

        if (empty($cookie['sub'])) {
            throw new Exception('User cookie missing');
        }

        $redirectURI = $this->config->getRedirectURI();
        $parts = parse_url($redirectURI);

        // Build origin (scheme + host + optional port)
        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        // Add trailing slash
        $defaultTargetURI = $origin . '/';

        // Safely pull inviter name and app name
        $inviterName = $cookie['name'] ?? $authArr['name'] ?? 'Someone';
        $appName     = $params['app_name'] ?? 'your app';

        // Default prompt if none provided
        $defaultPrompt = sprintf('%s has invited you to join %s', $inviterName, $appName);

        $request = [
            'app_name'           => $params['app_name'] ?? null,
            'prompt'             => $params['prompt'] ?? $defaultPrompt,
            'role'               => $params['role'] ?? null,
            'tenant'             => $params['tenant'] ?? null,
            'state'              => $params['state'] ?? null,
            'inviter'            => $cookie['sub'], // TODO: expose via a getter on AuthLib if preferred
            'client_id'          => $this->config->getClientId(),
            'initiate_login_uri' => $this->config->getRedirectURI() ?? '/', // TODO: confirm correct source
            'return_uri'         => $params['target_uri'] ?? $defaultTargetURI,
        ];

        $queryString = http_build_query($request);
        return "https://wallet.{$this->config->getHelloDomain()}/invite?" . $queryString;
    }
}

<?php

namespace HelloCoop\Handler;

use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\Auth as AuthLib;
use HelloCoop\Lib\OIDCManager;
use HelloCoop\Lib\Crypto;

class Invite
{
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private ConfigInterface $config;
    private AuthLib $authLib;
    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        ConfigInterface $config
    ) {
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->config = $config;
    }

    private function getAuthLib(): AuthLib
    {
        if ($this->authLib instanceof AuthLib) {
            return $this->authLib;
        }
        $crypto = new Crypto($this->config->getSecret());
        return $this->authLib = new AuthLib(
            $this->helloRequest,
            $this->helloResponse,
            $this->config,
            new OIDCManager(
                $this->helloRequest,
                $this->helloResponse,
                $this->config,
                $crypto
            ),
            $crypto
        );
    }

    public function generateInviteUrl(): string
    {
        $params = $this->helloRequest->fetchMultiple([
            'target_uri',
            'app_name',
            'prompt',
            'role',
            'tenant',
            'state',
            'redirect_uri'
        ]);

        $auth = $this->getAuthLib()->getAuthfromCookies();
        $request = [
            'app_name' => $params['app_name'],
            'prompt' => $params['prompt'],
            'role' =>  $params['role'],
            'tenant' => $params['tenant'],
            'state' => $params['state'],
            'inviter' => $auth->toArray()['sub'], //TODO: add a getter function for this value.
            'client_id' => $this->config->getClientId(),
            'initiate_login_uri' => $this->config->getRedirectURI() ?? '/', //TODO: need to fix this
            'return_uri' => $params['target_uri']
        ];

        $queryString = http_build_query($request);
        $url = "https://{$this->config->getHelloDomain()}/invite?" . $queryString;
        return $url;
    }
}

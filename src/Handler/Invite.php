<?php

namespace HelloCoop\Handler;

use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\Auth as AuthLib;

class Invite
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
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->config = $config;
    }

    private function getAuthLib(): AuthLib
    {
        return $this->authLib ??= new AuthLib(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
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
        if (empty($auth->toArray()['authCookie'])) {
            throw new \Exception("User not logged in");
        }

        $request = [
            'app_name' => $params['app_name'],
            'prompt' => $params['prompt'],
            'role' =>  $params['role'],
            'tenant' => $params['tenant'],
            'state' => $params['state'],
            'inviter' => $auth->toArray()['authCookie']['sub'], //TODO: add a getter function for this value.
            'client_id' => $this->config->getClientId(),
            'initiate_login_uri' => $this->config->getRedirectURI() ?? '/', //TODO: need to fix this
            'return_uri' => $params['target_uri']
        ];

        $queryString = http_build_query($request);
        $url = "https://wallet.{$this->config->getHelloDomain()}/invite?" . $queryString;
        return $url;
    }
}

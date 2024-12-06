<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\ConfigInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Lib\Auth;

class Invite
{
    private ConfigInterface $config;

    private Auth $auth;
    private HelloRequestInterface $helloRequest;
    public function __construct(ConfigInterface $config, Auth $auth, HelloRequestInterface $helloRequest)
    {
        $this->config = $config;
        $this->auth = $auth;
        $this->helloRequest = $helloRequest;
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

        $auth = $this->auth->getAuthfromCookies();
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

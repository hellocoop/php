<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\HelloConfig;
use HelloCoop\RequestParamFetcher\ParamFetcherInterface;
use HelloCoop\Lib\Auth;

class Invite
{
    private HelloConfig $config;

    private Auth $auth;
    private ParamFetcherInterface $paramFetcher;
    public function __construct(HelloConfig $config, Auth $auth, ParamFetcherInterface $paramFetcher)
    {
        $this->config = $config;
        $this->auth = $auth;
        $this->paramFetcher = $paramFetcher;
    }

    public function generateInviteUrl(): string
    {
        $params = $this->paramFetcher->fetchMultiple([
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

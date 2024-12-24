<?php

namespace HelloCoop\Handler;

use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\Auth as AuthLib;

/**
 * Handles user logout functionality, including generating the logout URL and clearing authentication cookies.
 */
class Logout
{
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private ConfigInterface $config;
    private ?AuthLib $authLib = null;

    /**
     * Constructor for the Logout class.
     *
     * @param HelloRequestInterface $helloRequest The request object for fetching data.
     * @param HelloResponseInterface $helloResponse The response object for sending data.
     * @param ConfigInterface $config The configuration object.
     */
    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        ConfigInterface $config
    ) {
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->config = $config;
    }

    /**
     * Retrieves the AuthLib instance.
     *
     * @return AuthLib The authentication library instance.
     */
    private function getAuthLib(): AuthLib
    {
        return $this->authLib ??= new AuthLib(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
        );
    }

    /**
     * Generates the URL to redirect to after logout.
     *
     * @return string The logout redirect URL.
     */
    public function generateLogoutUrl(): string
    {
        /** @var string|null $targetUri */
        $targetUri = $this->helloRequest->fetch('target_uri');
        $this->getAuthLib()->clearAuthCookie();
        if ($this->config->getLogoutSync()) {
            // Call the logoutSync callback
            call_user_func($this->config->getLogoutSync());
        }
        return $targetUri ?? $this->config->getRoutes()['loggedOut'];
    }
}

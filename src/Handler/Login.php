<?php

namespace HelloCoop\Handler;

class Login{
    //TODO: we can use a builder patter here
    public function __construct(
        string $redirectURI,
        string $clientId,
        string $scope,
        string $providerHint,
        string $loginHint,
        string $domainHint,
        string $prompt,
        string $providedNonce,
        string $helloWallet
    )
    {

    }

    public function createLoginRedirectURL(): string 
    {
       return ""; 
    }
}

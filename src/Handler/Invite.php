<?php

namespace HelloCoop\Handler;

class Invite{
    //TODO: we can use a builder patter here
    public function __construct(
        string $appName,
        string $prompt,
        string $role,
        string $tenant,
        string $state,
        string $inviter,
        string $clientId,
        string $initiateLoginUri,
        string $returnUri
    )
    {

    }

    public function generateInviteUrl(): string 
    {
       return ""; 
    }
}

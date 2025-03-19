<?php

declare(strict_types=1);

namespace HelloCoop\Lib;

final class IssuerRegistry
{
    /**
     * @return array<string, OpenIDProviderMetadata>
     */
    public static function getIssuers(): array
    {
        return [
            'http://mockin:3333' => new OpenIDProviderMetadata(
                'http://mockin:3333',
                'http://mockin:3333/oauth/introspect'
            ),
            'http://127.0.0.1:3333' => new OpenIDProviderMetadata(
                'http://127.0.0.1:3333',
                'http://127.0.0.1:3333/oauth/introspect'
            ),
            'https://issuer.hello.coop' => new OpenIDProviderMetadata(
                'https://issuer.hello.coop',
                'https://wallet.hello.coop/oauth/introspect'
            ),
        ];
    }
}

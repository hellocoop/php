<?php

declare(strict_types=1);

namespace HelloCoop\Lib;

final class OpenIDProviderMetadata
{
    public function __construct(
        public readonly string $issuer,
        public readonly string $introspection_endpoint,
        public readonly ?string $jwks_uri = null
    ) {}
}

<?php

declare(strict_types=1);

namespace HelloCoop\Utils;

class PackageMetadata
{
    /**
     * @return array{name: string, version: string}
     */
    public static function getMetadata(): array
    {
        return [
            'name' => 'hello-package',
            'version' => '1.0.0',
        ];
    }
}

<?php

namespace HelloCoop\Utils;

class PackageMetadata
{
    public static function getMetadata(): array
    {
        return [
            'name' => 'hello-package',
            'version' => '1.0.0',
        ];
    }
}
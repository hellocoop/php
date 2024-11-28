<?php

namespace HelloCoop\Cookie;

interface CookieManagerInterface
{
    public function set(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true
    ): void;

    public function get(string $name): ?string;

    public function delete(string $name, string $path = '/', string $domain = ''): void;
}
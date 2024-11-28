<?php

namespace HelloCoop\Cookie;

class CookieManager implements CookieManagerInterface
{
    public function set(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true
    ): void {
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax'
        ]);
    }

    public function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    public function delete(string $name, string $path = '/', string $domain = ''): void
    {
        $this->set($name, '', time() - 3600, $path, $domain);
    }
}
<?php

namespace HelloCoop\HelloResponse;

use HelloCoop\HelloResponse\HelloResponseInterface;

class HelloResponse implements HelloResponseInterface
{
    /**
     * Set a header.
     *
     * @param string $name The name of the header.
     * @param string|string[] $value The value(s) of the header.
     * @return void
     */
    public function setHeader(string $name, $value): void
    {
        // Ensure the value is an array to handle multiple values for the same header
        if (is_array($value)) {
            $value = implode(", ", $value); // Combine array values into a single string
        }

        // Send the header using PHP's header() function
        header("$name: $value", true);
    }

    public function deleteCookie(string $name, string $path = '/', string $domain = ''): void
    {
        $this->setCookie($name, '', time() - 3600, $path, $domain);
    }

    public function setCookie(
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
}

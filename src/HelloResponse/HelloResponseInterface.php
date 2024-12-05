<?php

namespace HelloCoop\HelloResponse;

/**
 * Interface for handling HTTP response headers and cookies.
 */
interface HelloResponseInterface
{
    /**
     * Set a header.
     *
     * @param string $name The name of the header.
     * @param string|string[] $value The value(s) of the header.
     * @return void
     */
    public function setHeader(string $name, $value): void;

    /**
     * Set a cookie.
     *
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * @param int $expire The expiration time of the cookie as a Unix timestamp. Default is 0 (session cookie).
     * @param string $path The path on the server where the cookie will be available. Default is '/'.
     * @param string $domain The domain that the cookie is available to. Default is an empty string.
     * @param bool $secure Indicates if the cookie should only be transmitted over a secure HTTPS connection. Default is false.
     * @param bool $httponly When true, makes the cookie accessible only through the HTTP protocol, restricting access from JavaScript. Default is true.
     * @return void
     */
    public function setCookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true
    ): void;

    /**
     * Delete a cookie.
     *
     * @param string $name The name of the cookie to delete.
     * @param string $path The path on the server where the cookie is available. Default is '/'.
     * @param string $domain The domain that the cookie is available to. Default is an empty string.
     * @return void
     */
    public function deleteCookie(string $name, string $path = '/', string $domain = ''): void;
}

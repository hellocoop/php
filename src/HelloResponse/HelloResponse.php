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

    /**
     * Set the HTTP status code.
     *
     * @param int $code HTTP status code.
     * @return void
     */
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Sends an empty HTTP response with the previously set headers.
     *
     * @return void
     */
    public function send(): void
    {
        http_response_code(200); // Ensure 200 OK status
        exit; // Stop execution to prevent unwanted output
    }

    /**
     * Deletes a cookie by setting its expiration time to the past.
     */
    public function deleteCookie(string $name, string $path = '/', string $domain = ''): void
    {
        $this->setCookie($name, '', time() - 3600, $path, $domain);
    }

    /**
     * Sets a cookie with the specified parameters.
     */
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

    /**
     * Redirects the user to the specified URL.
     */
    public function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Renders the given content as a plain string response.
     */
    public function render(string $content): string
    {
        return $content;
    }

    /**
     * Converts the given data array into a JSON string.
     */
    public function json(array $data): string
    {
        return json_encode($data) ?: "";
    }
}

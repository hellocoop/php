<?php

namespace HelloCoop\HelloRequest;

use HelloCoop\HelloRequest\HelloRequestInterface;

class HelloRequest implements HelloRequestInterface
{
    /**
     * Check if a parameter exists in either GET or POST data.
     *
     * @param string $key The key of the parameter to check.
     * @return bool True if the key exists, false otherwise.
     */
    #[\Override]
    public function has(string $key): bool
    {
        return isset($_GET[$key]) || isset($_POST[$key]);
    }

    /**
     * Fetch a parameter by key from either GET or POST data.
     *
     * @param string $key The key of the parameter to fetch.
     * @param mixed $default Default value if the key is not found.
     * @return mixed The value of the parameter or default.
     */
    #[\Override]
    public function fetch(string $key, $default = null)
    {
        // First check GET, then POST if not found.
        return $_GET[$key] ?? $_POST[$key] ?? $default;
    }

    /**
     * Fetch multiple parameters by keys from either GET or POST data.
     *
     * @param array<string> $keys The keys of the parameters to fetch.
     * @return array<string, mixed> An associative array of parameters.
     */
    #[\Override]
    public function fetchMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->fetch($key);  // Use the fetch method to get each parameter
        }
        return $result;
    }

    /**
     * Fetch a header by key from the request headers.
     *
     * @param string $key The key of the header to fetch.
     * @param mixed|null $default Default value if the key is not found.
     * @return mixed|null The value of the header or default.
     */
    #[\Override]
    public function fetchHeader(string $key, $default = null)
    {
        $headers = $this->getAllHeaders();
        $normalizedKey = strtolower($key);
        foreach ($headers as $headerKey => $headerValue) {
            if (strtolower($headerKey) === $normalizedKey) {
                return $headerValue;
            }
        }
        return $default;
    }

    /**
     * Retrieve all request headers.
     *
     * @return array<string, mixed> An associative array of all request headers.
     */
    private function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $header = str_replace('_', '-', substr($name, 5));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    /**
     * Retrieve the value of a cookie by its name.
     *
     * @param string $name The name of the cookie to retrieve.
     * @return string|null The value of the cookie if it exists, or null if it does not.
     */
    #[\Override]
    public function getCookie(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    /**
     * Retrieves the current request URI from the server.
     *
     * This method fetches the request URI from the `$_SERVER` superglobal,
     * which includes the path and query string of the current HTTP request.
     * It is useful for identifying the requested resource or endpoint.
     *
     * @return string The current request URI as provided by the server.
     */
    #[\Override]
    public function getRequestUri(): string
    {
        return $_SERVER["REQUEST_URI"];
    }

    #[\Override]
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}

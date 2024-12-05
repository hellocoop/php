<?php

namespace HelloCoop\HelloRequest;

interface HelloRequestInterface
{
    /**
     * Fetch a parameter by key from either GET or POST data.
     *
     * @param string $key The key of the parameter to fetch.
     * @param mixed $default Default value if the key is not found.
     * @return mixed The value of the parameter or default.
     */
    public function fetch(string $key, $default = null): ?string;

    /**
     * Fetch multiple parameters by keys from either GET or POST data.
     *
     * @param array $keys The keys of the parameters to fetch.
     * @return array An associative array of parameters.
     */
    public function fetchMultiple(array $keys): array;

    /**
     * Fetch a header by key from the request headers.
     *
     * @param string $key The key of the header to fetch.
     * @param mixed $default Default value if the key is not found.
     * @return mixed The value of the header or default.
     */
    public function fetchHeader(string $key, $default = null): ?string;

    /**
     * Fetch a cookie value by name.
     *
     * @param string $name The name of the cookie to retrieve.
     * @return string|null The value of the cookie if found, or null if it doesn't exist.
     */
    public function getCookie(string $name): ?string;
}

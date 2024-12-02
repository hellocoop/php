<?php

namespace HelloCoop\RequestParamFetcher;

interface ParamFetcherInterface
{
    /**
     * Fetch a parameter by key from either GET or POST data.
     *
     * @param string $key The key of the parameter to fetch.
     * @param mixed $default Default value if the key is not found.
     * @return mixed The value of the parameter or default.
     */
    public function fetch(string $key, $default = null);

    /**
     * Fetch multiple parameters by keys from either GET or POST data.
     *
     * @param array $keys The keys of the parameters to fetch.
     * @return array An associative array of parameters.
     */
    public function fetchMultiple(array $keys): array;
}

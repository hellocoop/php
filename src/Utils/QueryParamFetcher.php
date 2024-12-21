<?php

namespace HelloCoop\Utils;

class QueryParamFetcher
{
    /**
     * @param array<string, mixed> $keys
     * @return array<string, mixed>
     */
    public static function fetch(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $_GET[$key] ?? null;
        }
        return $result;
    }
}

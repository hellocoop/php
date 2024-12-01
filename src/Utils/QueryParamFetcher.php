<?php

namespace HelloCoop\Utils;

class QueryParamFetcher
{
    public static function fetch(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $_GET[$key] ?? null;
        }
        return $result;
    }
}

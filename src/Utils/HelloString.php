<?php

namespace HelloCoop\Utils;

class HelloString
{
    public static function startsWith(string $haystack, string $needle): bool
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }
    public static function endsWith(string $haystack, string $needle): bool
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
}

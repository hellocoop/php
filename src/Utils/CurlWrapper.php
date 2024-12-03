<?php

namespace HelloCoop\Utils;

class CurlWrapper
{
    public function init(string $url)
    {
        return curl_init($url);
    }

    public function setOpt($ch, int $option, $value): bool
    {
        return curl_setopt($ch, $option, $value);
    }

    public function exec($ch)
    {
        return curl_exec($ch);
    }

    public function getInfo($ch, int $option)
    {
        return curl_getinfo($ch, $option);
    }

    public function close($ch): void
    {
        curl_close($ch);
    }

    public function error($ch): string
    {
        return curl_error($ch);
    }
}

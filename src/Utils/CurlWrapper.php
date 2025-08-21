<?php

namespace HelloCoop\Utils;

// XXX: phpstan ignores can be removed when 7.x support is dropped in library.
class CurlWrapper
{
    /**
     * @param string $url
     */
    public function init(string $url): \CurlHandle|false
    {
        return curl_init($url);
    }

    /**
     * @param resource $ch
     * @param int $option
     * @param mixed $value
     * @return bool
     */
    public function setOpt($ch, int $option, $value): bool
    {
        return curl_setopt($ch, $option, $value); // @phpstan-ignore argument.type
    }

    /**
     * @param resource $ch
     * @return bool|string
     */
    public function exec($ch)
    {
        return curl_exec($ch); // @phpstan-ignore argument.type
    }

    /**
     * @param resource $ch
     * @param int $option
     * @return mixed
     */
    public function getInfo($ch, int $option)
    {
        return curl_getinfo($ch, $option); // @phpstan-ignore argument.type
    }

    /**
     * @param resource $ch
     * @return void
     */
    public function close($ch): void
    {
        curl_close($ch); // @phpstan-ignore argument.type
    }

    /**
     * @param resource $ch
     * @return string
     */
    public function error($ch): string
    {
        return curl_error($ch); // @phpstan-ignore argument.type
    }
}

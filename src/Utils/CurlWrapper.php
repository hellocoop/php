<?php

namespace HelloCoop\Utils;

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
     * @param \CurlHandle $ch
     * @param int $option
     * @param mixed $value
     * @return bool
     */
    public function setOpt($ch, int $option, $value): bool
    {
        return curl_setopt($ch, $option, $value);
    }

    /**
     * @param \CurlHandle $ch
     * @return bool|string
     */
    public function exec($ch)
    {
        return curl_exec($ch);
    }

    /**
     * @param \CurlHandle $ch
     * @param int $option
     * @return mixed
     */
    public function getInfo($ch, int $option)
    {
        return curl_getinfo($ch, $option);
    }

    /**
     * @param \CurlHandle $ch
     * @return void
     */
    public function close($ch): void
    {
        curl_close($ch);
    }

    /**
     * @param \CurlHandle $ch
     * @return string
     */
    public function error($ch): string
    {
        return curl_error($ch);
    }
}

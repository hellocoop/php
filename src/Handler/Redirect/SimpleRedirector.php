<?php

namespace HelloCoop\Handler\Redirect;

use HelloCoop\Handler\Redirect\RedirectorInterface;

class SimpleRedirector implements RedirectorInterface
{
    public function redirect(string $url): void
    {
        header('Location: ' . $url);
        if (defined('TESTING')) {
            throw new \RuntimeException('Exit called');
        }
        exit;
    }
}

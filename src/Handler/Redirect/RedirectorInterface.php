<?php

namespace HelloCoop\Handler\Redirect;

interface RedirectorInterface
{
    public function redirect(string $url): void;
}

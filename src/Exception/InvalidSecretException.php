<?php

namespace HelloCoop\Exception;

use Exception;

class InvalidSecretException extends Exception
{
    protected $message = 'Invalid secret key. Must be a 64-character hexadecimal string.';
}

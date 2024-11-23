<?php

namespace HelloCoop\Exception;

use Exception;

class CryptoFailedException extends Exception
{
    protected $message = 'Crypto failed. There was an error encrypting the data.';
}

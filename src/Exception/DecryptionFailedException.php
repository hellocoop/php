<?php

namespace HelloCoop\Exception;

use Exception;

class DecryptionFailedException extends Exception
{
    protected $message = 'Decryption failed. The data may be corrupted or the wrong key was used.';
}

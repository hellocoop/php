<?php

namespace HelloCoop\Exception;

use Throwable;
use Exception;

class CallbackException extends Exception
{
    private array $errorDetails;

    public function __construct(
        array $errorDetails,
        $message = "Callback Exception",
        $code = 0,
        Throwable $previous = null
    ) {
        $this->errorDetails = $errorDetails;
        parent::__construct($message, $code, $previous);
    }

    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }
}

<?php

namespace Fintech\Ekyc\Exceptions;

use Exception;
use Throwable;

/**
 * Class EkycException
 */
class EkycException extends Exception
{
    /**
     * CoreException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     */
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

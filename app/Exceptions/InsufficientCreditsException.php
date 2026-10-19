<?php

namespace App\Exceptions;

use Exception;

class InsufficientCreditsException extends Exception
{
    protected $message = 'This user has no available credits.';
}

<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Base exception for league-related errors
 */
class LeagueException extends Exception
{
    /**
     * Create a new league exception
     */
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

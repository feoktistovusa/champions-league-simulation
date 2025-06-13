<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception thrown when a requested match cannot be found
 */
class MatchNotFoundException extends LeagueException
{
    /**
     * Create exception for match not found by ID
     *
     * @param  int  $matchId  The ID of the match that was not found
     */
    public static function withId(int $matchId): self
    {
        return new self("Match with ID {$matchId} not found");
    }
}

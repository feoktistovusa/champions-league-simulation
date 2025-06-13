<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception thrown when an invalid number of teams is provided for league operations
 */
class InvalidTeamCountException extends LeagueException
{
    /**
     * Create exception for insufficient teams
     *
     * @param  int  $required  Minimum required teams
     * @param  int  $actual  Actual number of teams
     */
    public static function insufficient(int $required, int $actual): self
    {
        return new self(
            "At least {$required} teams are required for the league, {$actual} found"
        );
    }

    /**
     * Create exception for odd number of teams
     *
     * @param  int  $teamCount  Number of teams provided
     */
    public static function oddNumber(int $teamCount): self
    {
        return new self(
            "An even number of teams is required for round-robin fixtures, {$teamCount} provided"
        );
    }
}

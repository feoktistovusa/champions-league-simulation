<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface StandingRepositoryInterface
{
    /**
     * Get league standings ordered by position
     */
    public function getLeagueStandings(): Collection;

    /**
     * Reset all standings to initial state
     */
    public function resetAll(): int;

    /**
     * Find standing by team ID
     */
    public function findByTeamId(int $teamId);

    /**
     * Update standing
     */
    public function update(int $id, array $data): bool;
}

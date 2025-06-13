<?php

namespace App\Repositories\Contracts;

use App\Models\GameMatch;
use Illuminate\Database\Eloquent\Collection;

interface MatchRepositoryInterface
{
    /**
     * Get matches with teams, optionally filtered by week
     */
    public function getMatchesWithTeams(?int $week = null): Collection;

    /**
     * Get current week data
     */
    public function getCurrentWeek(): array;

    /**
     * Find a match by ID
     */
    public function find(int $id): ?GameMatch;

    /**
     * Update a match
     */
    public function update(int $id, array $data): bool;

    /**
     * Get matches for a specific week
     */
    public function getByWeek(int $week): Collection;

    /**
     * Get unplayed matches
     */
    public function getUnplayed(): Collection;

    /**
     * Count total matches
     */
    public function count(): int;

    /**
     * Delete all matches
     */
    public function truncate(): void;

    /**
     * Mass insert matches
     */
    public function insert(array $data): bool;
}

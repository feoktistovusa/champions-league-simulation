<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TeamRepositoryInterface
{
    /**
     * Get all teams
     */
    public function all(): Collection;

    /**
     * Find a team by ID
     */
    public function find(int $id);

    /**
     * Count total teams
     */
    public function count(): int;
}

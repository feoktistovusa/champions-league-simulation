<?php

namespace App\Repositories\Eloquent;

use App\Models\Standing;
use App\Repositories\Contracts\StandingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StandingRepository implements StandingRepositoryInterface
{
    protected Standing $model;

    public function __construct(Standing $model)
    {
        $this->model = $model;
    }

    public function getLeagueStandings(): Collection
    {
        return $this->model->getLeagueStandings();
    }

    public function resetAll(): int
    {
        return $this->model->resetAll();
    }

    public function findByTeamId(int $teamId)
    {
        return $this->model->where('team_id', $teamId)->first();
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data) > 0;
    }
}

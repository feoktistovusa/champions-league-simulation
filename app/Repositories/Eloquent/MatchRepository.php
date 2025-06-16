<?php

namespace App\Repositories\Eloquent;

use App\Models\GameMatch;
use App\Repositories\Contracts\MatchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MatchRepository implements MatchRepositoryInterface
{
    protected GameMatch $model;

    public function __construct(GameMatch $model)
    {
        $this->model = $model;
    }

    public function getMatchesWithTeams(?int $week = null): Collection
    {
        return $this->model->getMatchesWithTeams($week);
    }

    public function getCurrentWeek(): array
    {
        return $this->model->getCurrentWeek();
    }

    public function find(int $id): ?GameMatch
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data) > 0;
    }

    public function getByWeek(int $week): Collection
    {
        return $this->model->forWeek($week)->with(['homeTeam', 'awayTeam'])->get();
    }

    public function getUnplayed(): Collection
    {
        return $this->model->where('played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->get();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function getPlayedMatchesCount(): int
    {
        return $this->model->where('played', true)->count();
    }

    public function truncate(): void
    {
        $this->model->truncate();
    }

    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }
}

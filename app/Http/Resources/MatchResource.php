<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'home_team_id' => $this->resource->home_team_id,
            'away_team_id' => $this->resource->away_team_id,
            'home_team' => [
                'id' => $this->resource->homeTeam->id,
                'name' => $this->resource->homeTeam->name,
                'strength' => $this->resource->homeTeam->strength,
                'logo' => $this->resource->homeTeam->logo,
            ],
            'away_team' => [
                'id' => $this->resource->awayTeam->id,
                'name' => $this->resource->awayTeam->name,
                'strength' => $this->resource->awayTeam->strength,
                'logo' => $this->resource->awayTeam->logo,
            ],
            'home_score' => $this->resource->home_score,
            'away_score' => $this->resource->away_score,
            'week' => $this->resource->week,
            'played' => $this->resource->played,
        ];
    }
}

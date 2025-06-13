<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandingResource extends JsonResource
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
            'team_id' => $this->resource->team_id,
            'team' => [
                'id' => $this->resource->team->id,
                'name' => $this->resource->team->name,
                'strength' => $this->resource->team->strength,
                'logo' => $this->resource->team->logo,
            ],
            'played' => $this->resource->played,
            'won' => $this->resource->won,
            'drawn' => $this->resource->drawn,
            'lost' => $this->resource->lost,
            'goals_for' => $this->resource->goals_for,
            'goals_against' => $this->resource->goals_against,
            'goal_difference' => $this->resource->goal_difference,
            'points' => $this->resource->points,
        ];
    }
}

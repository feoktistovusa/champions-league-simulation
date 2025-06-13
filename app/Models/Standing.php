<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Standing
 *
 * Represents a team's standing/statistics in the league table
 *
 * @property int $id
 * @property int $team_id
 * @property int $played Number of matches played
 * @property int $won Number of matches won
 * @property int $drawn Number of matches drawn
 * @property int $lost Number of matches lost
 * @property int $goals_for Goals scored by the team
 * @property int $goals_against Goals conceded by the team
 * @property int $goal_difference Goal difference (goals_for - goals_against)
 * @property int $points Total points earned (3 for win, 1 for draw)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Team $team
 */
class Standing extends Model
{
    /**
     * Point values for match results
     */
    public const POINTS_FOR_WIN = 3;

    public const POINTS_FOR_DRAW = 1;

    public const POINTS_FOR_LOSS = 0;

    /**
     * The attributes that are mass assignable
     *
     * @var list<string>
     */
    protected $fillable = [
        'team_id',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'team_id' => 'integer',
        'played' => 'integer',
        'won' => 'integer',
        'drawn' => 'integer',
        'lost' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'goal_difference' => 'integer',
        'points' => 'integer',
    ];

    /**
     * Get the team this standing belongs to
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope to order standings by league position
     *
     * Orders by points (descending), then goal difference (descending),
     * then goals for (descending) as tie-breakers
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc');
    }

    /**
     * Get current league standings with teams
     *
     * Returns all standings ordered by league position with team data
     *
     * @return Collection<int, static>
     */
    public static function getLeagueStandings(): Collection
    {
        return static::with('team')
            ->ordered()
            ->get();
    }

    /**
     * Reset all standings to initial state
     *
     * Sets all standing statistics back to zero for all teams
     *
     * @return int Number of rows affected
     */
    public static function resetAll(): int
    {
        return static::query()->update([
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
        ]);
    }
}

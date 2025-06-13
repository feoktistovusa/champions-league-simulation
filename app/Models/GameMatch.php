<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GameMatch
 *
 * Represents a football match between two teams in the Champions League
 *
 * @property int $id
 * @property int $home_team_id
 * @property int $away_team_id
 * @property int|null $home_score
 * @property int|null $away_score
 * @property int $week
 * @property bool $played
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Team $homeTeam
 * @property-read Team $awayTeam
 */
class GameMatch extends Model
{
    /**
     * League configuration constants
     */
    public const MATCHES_PER_WEEK = 2;

    public const TOTAL_WEEKS = 6;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'matches';

    /**
     * The attributes that are mass assignable
     *
     * @var list<string>
     */
    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'week',
        'played',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'played' => 'boolean',
        'home_team_id' => 'integer',
        'away_team_id' => 'integer',
        'home_score' => 'integer',
        'away_score' => 'integer',
        'week' => 'integer',
    ];

    /**
     * Get the home team for the match
     *
     * @return BelongsTo<Team, $this>
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team for the match
     *
     * @return BelongsTo<Team, $this>
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Get the current week number based on played matches
     *
     * Calculates the current week in the league based on the number of matches played.
     * Uses constants for matches per week and total weeks.
     *
     * @return array<string, mixed> Array containing current_week, total_weeks, and all_matches_played
     */
    public static function getCurrentWeek(): array
    {
        $playedMatches = static::where('played', true)->count();

        $currentWeek = (int) ceil($playedMatches / self::MATCHES_PER_WEEK) + 1;

        if ($currentWeek > self::TOTAL_WEEKS) {
            $currentWeek = self::TOTAL_WEEKS;
        }

        return [
            'current_week' => $currentWeek,
            'total_weeks' => self::TOTAL_WEEKS,
            'all_matches_played' => $playedMatches === static::count(),
        ];
    }

    /**
     * Scope to filter matches by week
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @param  int  $week  The week number to filter by
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeForWeek($query, int $week)
    {
        return $query->where('week', $week);
    }

    /**
     * Get matches with teams, optionally filtered by week
     *
     * Retrieves matches with their associated home and away teams.
     * Can be filtered by week number if provided.
     *
     * @param  int|null  $week  Optional week number to filter by
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function getMatchesWithTeams(?int $week = null)
    {
        $query = static::with(['homeTeam', 'awayTeam']);

        if ($week !== null) {
            $query->forWeek($week);
        }

        return $query->orderBy('week')->orderBy('id')->get();
    }
}

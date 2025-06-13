<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Team
 *
 * Represents a football team in the Champions League
 *
 * @property int $id
 * @property string $name
 * @property int $strength Team strength rating (used for match simulation)
 * @property string|null $logo Team logo filename
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Collection<int, GameMatch> $homeMatches
 * @property-read Collection<int, GameMatch> $awayMatches
 * @property-read Standing $standing
 */
class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'strength', 'logo'];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'strength' => 'integer',
    ];

    /**
     * Get all home matches for this team
     *
     * @return HasMany<GameMatch, $this>
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'home_team_id');
    }

    /**
     * Get all away matches for this team
     *
     * @return HasMany<GameMatch, $this>
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'away_team_id');
    }

    /**
     * Get the standing record for this team
     *
     * @return HasOne<Standing, $this>
     */
    public function standing(): HasOne
    {
        return $this->hasOne(Standing::class);
    }

    /**
     * Get all matches (both home and away) for this team
     *
     * Combines home and away matches into a single collection
     *
     * @return Collection<int, GameMatch>
     */
    public function allMatches(): Collection
    {
        return $this->homeMatches->merge($this->awayMatches);
    }
}

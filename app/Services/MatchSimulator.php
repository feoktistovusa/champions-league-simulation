<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Standing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class MatchSimulator
 *
 * Handles the simulation of football matches and updates team standings
 */
class MatchSimulator
{
    /**
     * Simulation configuration constants
     */
    private const HOME_ADVANTAGE = 10;

    private const STRENGTH_DIVISOR = 30;

    private const GOAL_PROBABILITY_MULTIPLIER = 20;

    private const MAX_GOAL_ATTEMPTS = 5;

    private const PROBABILITY_RANGE_MIN = 1;

    private const PROBABILITY_RANGE_MAX = 100;

    /**
     * Simulate a single match between two teams
     *
     * Calculates goals based on team strength and home advantage,
     * then updates the match record and team standings
     *
     * @param  GameMatch  $match  The match to simulate
     * @return GameMatch The updated match with scores
     */
    public function simulateMatch(GameMatch $match): GameMatch
    {
        return DB::transaction(function () use ($match) {
            $match->refresh();

            if ($match->played) {
                return $match;
            }

            $homeTeam = $match->homeTeam;
            $awayTeam = $match->awayTeam;

            $homeStrength = $homeTeam->strength + self::HOME_ADVANTAGE;
            $awayStrength = $awayTeam->strength;

            $homeGoals = $this->generateGoals($homeStrength);
            $awayGoals = $this->generateGoals($awayStrength);

            $match->update([
                'home_score' => $homeGoals,
                'away_score' => $awayGoals,
                'played' => true,
            ]);

            $this->updateStandings($match);

            return $match;
        });
    }

    /**
     * Generate goals for a team based on their strength
     *
     * Uses a probabilistic approach where stronger teams
     * have higher chances of scoring goals
     *
     * @param  int  $strength  Team strength rating
     * @return int Number of goals scored
     */
    private function generateGoals(int $strength): int
    {
        $avgGoals = $strength / self::STRENGTH_DIVISOR;
        $goals = 0;

        for ($i = 0; $i < self::MAX_GOAL_ATTEMPTS; $i++) {
            $probability = $avgGoals * self::GOAL_PROBABILITY_MULTIPLIER;
            if (rand(self::PROBABILITY_RANGE_MIN, self::PROBABILITY_RANGE_MAX) < $probability) {
                $goals++;
            }
        }

        return $goals;
    }

    /**
     * Update team standings after a match
     *
     * Updates statistics for both teams including:
     * - Matches played, won, drawn, lost
     * - Goals for/against and goal difference
     * - Points (3 for win, 1 for draw, 0 for loss)
     *
     * @param  GameMatch  $match  The completed match
     */
    private function updateStandings(GameMatch $match): void
    {
        $homeStanding = Standing::where('team_id', $match->home_team_id)->first();
        $awayStanding = Standing::where('team_id', $match->away_team_id)->first();

        if (! $homeStanding || ! $awayStanding) {
            throw new \RuntimeException('Standing records not found for teams in match ID: '.$match->id);
        }

        $homeStanding->played++;
        $awayStanding->played++;
        $homeStanding->goals_for += $match->home_score;
        $homeStanding->goals_against += $match->away_score;
        $awayStanding->goals_for += $match->away_score;
        $awayStanding->goals_against += $match->home_score;

        if ($match->home_score > $match->away_score) {
            $homeStanding->won++;
            $homeStanding->points += Standing::POINTS_FOR_WIN;
            $awayStanding->lost++;
        } elseif ($match->home_score < $match->away_score) {
            $awayStanding->won++;
            $awayStanding->points += Standing::POINTS_FOR_WIN;
            $homeStanding->lost++;
        } else {
            $homeStanding->drawn++;
            $awayStanding->drawn++;
            $homeStanding->points += Standing::POINTS_FOR_DRAW;
            $awayStanding->points += Standing::POINTS_FOR_DRAW;
        }
        $homeStanding->goal_difference = $homeStanding->goals_for - $homeStanding->goals_against;
        $awayStanding->goal_difference = $awayStanding->goals_for - $awayStanding->goals_against;

        $homeStanding->save();
        $awayStanding->save();
    }

    /**
     * Simulate all matches in a specific week
     *
     * Finds all unplayed matches for the given week and simulates them
     *
     * @param  int  $week  The week number to simulate
     * @return Collection<int, GameMatch> Collection of simulated matches
     */
    public function simulateWeek(int $week): Collection
    {
        $matches = GameMatch::where('week', $week)
            ->where('played', false)
            ->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match);
        }

        return $matches;
    }

    /**
     * Simulate all remaining unplayed matches
     *
     * Simulates all matches that haven't been played yet
     *
     * @return Collection<int, GameMatch> Collection of all simulated matches
     */
    public function simulateAllMatches(): Collection
    {
        $matches = GameMatch::where('played', false)->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match);
        }

        return $matches;
    }
}

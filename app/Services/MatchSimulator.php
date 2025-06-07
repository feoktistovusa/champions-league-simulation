<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Standing;
use Illuminate\Database\Eloquent\Collection;

class MatchSimulator
{
    public function simulateMatch(GameMatch $match): GameMatch
    {
        if ($match->played) {
            return $match;
        }

        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        $homeStrength = $homeTeam->strength;
        $awayStrength = $awayTeam->strength;

        $homeStrength += 10;

        $strengthDiff = $homeStrength - $awayStrength;
        $homeGoals = $this->generateGoals($homeStrength);
        $awayGoals = $this->generateGoals($awayStrength);

        $match->update([
            'home_score' => $homeGoals,
            'away_score' => $awayGoals,
            'played' => true,
        ]);

        $this->updateStandings($match);

        return $match;
    }

    private function generateGoals(int $strength): int
    {
        $avgGoals = $strength / 30;
        $goals = 0;

        for ($i = 0; $i < 5; $i++) {
            if (rand(1, 100) < ($avgGoals * 20)) {
                $goals++;
            }
        }

        return $goals;
    }

    private function updateStandings(GameMatch $match): void
    {
        $homeStanding = Standing::where('team_id', $match->home_team_id)->first();
        $awayStanding = Standing::where('team_id', $match->away_team_id)->first();

        $homeStanding->played++;
        $awayStanding->played++;

        $homeStanding->goals_for += $match->home_score;
        $homeStanding->goals_against += $match->away_score;
        $awayStanding->goals_for += $match->away_score;
        $awayStanding->goals_against += $match->home_score;

        if ($match->home_score > $match->away_score) {
            $homeStanding->won++;
            $homeStanding->points += 3;
            $awayStanding->lost++;
        } elseif ($match->home_score < $match->away_score) {
            $awayStanding->won++;
            $awayStanding->points += 3;
            $homeStanding->lost++;
        } else {
            $homeStanding->drawn++;
            $awayStanding->drawn++;
            $homeStanding->points += 1;
            $awayStanding->points += 1;
        }

        $homeStanding->goal_difference = $homeStanding->goals_for - $homeStanding->goals_against;
        $awayStanding->goal_difference = $awayStanding->goals_for - $awayStanding->goals_against;

        $homeStanding->save();
        $awayStanding->save();
    }

    public function simulateWeek(int $week): Collection
    {
        $matches = GameMatch::where('week', $week)->where('played', false)->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match);
        }

        return $matches;
    }

    public function simulateAllMatches(): Collection
    {
        $matches = GameMatch::where('played', false)->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match);
        }

        return $matches;
    }
}

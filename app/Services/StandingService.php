<?php

namespace App\Services;

use App\Models\GameMatch;

class StandingService
{
    /**
     * Reset standings for a match that was already played
     */
    public function resetMatchStandings(GameMatch $match): void
    {
        if (! $match->played) {
            return;
        }

        /** @var \App\Models\Standing|null $homeStanding */
        $homeStanding = $match->homeTeam->standing;
        /** @var \App\Models\Standing|null $awayStanding */
        $awayStanding = $match->awayTeam->standing;

        if (! $homeStanding || ! $awayStanding) {
            throw new \RuntimeException('Standing records not found for teams in match ID: '.$match->id);
        }

        $homeStanding->played--;
        $awayStanding->played--;

        $homeStanding->goals_for -= $match->home_score;
        $homeStanding->goals_against -= $match->away_score;
        $awayStanding->goals_for -= $match->away_score;
        $awayStanding->goals_against -= $match->home_score;

        if ($match->home_score > $match->away_score) {
            $homeStanding->won--;
            $homeStanding->points -= 3;
            $awayStanding->lost--;
        } elseif ($match->home_score < $match->away_score) {
            $awayStanding->won--;
            $awayStanding->points -= 3;
            $homeStanding->lost--;
        } else {
            $homeStanding->drawn--;
            $awayStanding->drawn--;
            $homeStanding->points -= 1;
            $awayStanding->points -= 1;
        }

        $homeStanding->goal_difference = $homeStanding->goals_for - $homeStanding->goals_against;
        $awayStanding->goal_difference = $awayStanding->goals_for - $awayStanding->goals_against;

        $homeStanding->save();
        $awayStanding->save();
    }
}

<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

class PredictionService
{
    public function calculateChampionshipProbabilities(): array
    {
        $teams = Team::with('standing')->get();
        $remainingMatches = GameMatch::where('played', false)->get();

        if ($remainingMatches->isEmpty()) {
            return $this->getCurrentProbabilities();
        }

        $simulations = 10000;
        $winCounts = [];

        foreach ($teams as $team) {
            $winCounts[$team->id] = 0;
        }

        for ($i = 0; $i < $simulations; $i++) {
            $simulatedStandings = $this->simulateSeason($teams, $remainingMatches);
            $winner = $simulatedStandings->first();
            $winCounts[$winner->team_id]++;
        }

        $probabilities = [];
        foreach ($teams as $team) {
            $probabilities[] = [
                'team' => $team,
                'probability' => round(($winCounts[$team->id] / $simulations) * 100, 2),
            ];
        }

        usort($probabilities, function ($a, $b) {
            return $b['probability'] <=> $a['probability'];
        });

        return $probabilities;
    }

    private function getCurrentProbabilities(): array
    {
        $standings = Standing::with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get();

        $probabilities = [];
        $first = true;

        foreach ($standings as $standing) {
            $probabilities[] = [
                'team' => $standing->team,
                'probability' => $first ? 100 : 0,
            ];
            $first = false;
        }

        return $probabilities;
    }

    private function simulateSeason(Collection $teams, Collection $remainingMatches): Collection
    {
        $simulatedStandings = [];

        foreach ($teams as $team) {
            $standing = clone $team->standing;
            $standing->team_id = $team->id;
            $simulatedStandings[$team->id] = $standing;
        }

        foreach ($remainingMatches as $match) {
            $result = $this->simulateMatchResult(
                $match->homeTeam,
                $match->awayTeam
            );

            $this->updateSimulatedStandings(
                $simulatedStandings,
                $match->home_team_id,
                $match->away_team_id,
                $result
            );
        }

        usort($simulatedStandings, function ($a, $b) {
            if ($a->points != $b->points) {
                return $b->points <=> $a->points;
            }
            if ($a->goal_difference != $b->goal_difference) {
                return $b->goal_difference <=> $a->goal_difference;
            }

            return $b->goals_for <=> $a->goals_for;
        });

        return new Collection($simulatedStandings);
    }

    private function simulateMatchResult(Team $homeTeam, Team $awayTeam): array
    {
        $homeStrength = $homeTeam->strength + 10;
        $awayStrength = $awayTeam->strength;

        $totalStrength = $homeStrength + $awayStrength;
        $homeWinProb = $homeStrength / $totalStrength;
        $drawProb = 0.25;

        $random = mt_rand() / mt_getrandmax();

        if ($random < $homeWinProb * (1 - $drawProb)) {
            return ['home' => mt_rand(1, 4), 'away' => mt_rand(0, 2)];
        } elseif ($random < (1 - $drawProb)) {
            return ['home' => mt_rand(0, 2), 'away' => mt_rand(1, 4)];
        } else {
            $drawScore = mt_rand(0, 3);

            return ['home' => $drawScore, 'away' => $drawScore];
        }
    }

    private function updateSimulatedStandings(array &$standings, int $homeId, int $awayId, array $result): void
    {
        $homeStanding = &$standings[$homeId];
        $awayStanding = &$standings[$awayId];

        $homeStanding->played++;
        $awayStanding->played++;

        $homeStanding->goals_for += $result['home'];
        $homeStanding->goals_against += $result['away'];
        $awayStanding->goals_for += $result['away'];
        $awayStanding->goals_against += $result['home'];

        if ($result['home'] > $result['away']) {
            $homeStanding->won++;
            $homeStanding->points += 3;
            $awayStanding->lost++;
        } elseif ($result['home'] < $result['away']) {
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
    }
}

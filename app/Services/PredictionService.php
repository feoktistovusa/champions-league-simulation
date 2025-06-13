<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PredictionService
 *
 * Handles championship probability calculations using Monte Carlo simulation
 */
class PredictionService
{
    /**
     * Number of simulations to run for probability calculation
     */
    private const SIMULATION_COUNT = 10000;

    /**
     * Calculate championship probabilities for all teams
     *
     * @return array<int, array{team: Team, probability: float}>
     */
    public function calculateChampionshipProbabilities(): array
    {
        /** @var Collection<int, Team> $teams */
        $teams = Team::with('standing')->get();
        /** @var Collection<int, GameMatch> $remainingMatches */
        $remainingMatches = GameMatch::where('played', false)->get();

        if ($remainingMatches->isEmpty()) {
            return $this->getCurrentProbabilities();
        }

        $winCounts = [];

        foreach ($teams as $team) {
            $winCounts[$team->id] = 0;
        }

        for ($i = 0; $i < self::SIMULATION_COUNT; $i++) {
            $simulatedStandings = $this->simulateSeason($teams, $remainingMatches);
            $winner = $simulatedStandings->first();
            if ($winner !== null && $winner instanceof Standing) {
                $winCounts[$winner->team_id]++;
            }
        }

        $probabilities = [];
        foreach ($teams as $team) {
            $probabilities[] = [
                'team' => $team,
                'probability' => round(($winCounts[$team->id] / self::SIMULATION_COUNT) * 100, 2),
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

        if ($standings->isEmpty()) {
            return [];
        }

        $probabilities = [];
        $topPoints = $standings->first()->points;
        $topGoalDifference = $standings->first()->goal_difference;
        $topGoalsFor = $standings->first()->goals_for;
        $topTeams = $standings->filter(function ($standing) use ($topPoints, $topGoalDifference, $topGoalsFor) {
            return $standing->points === $topPoints &&
                   $standing->goal_difference === $topGoalDifference &&
                   $standing->goals_for === $topGoalsFor;
        });

        $winnerProbability = $topTeams->count() > 1 ? round(100 / $topTeams->count(), 2) : 100;

        foreach ($standings as $standing) {
            $isWinner = $standing->points === $topPoints &&
                       $standing->goal_difference === $topGoalDifference &&
                       $standing->goals_for === $topGoalsFor;

            $probabilities[] = [
                'team' => $standing->team,
                'probability' => $isWinner ? $winnerProbability : 0,
            ];
        }

        return $probabilities;
    }

    /**
     * Simulate the remaining season for probability calculation
     *
     * @param  Collection<int, Team>  $teams
     * @param  Collection<int, GameMatch>  $remainingMatches
     * @return Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    private function simulateSeason(Collection $teams, Collection $remainingMatches): Collection
    {
        $simulatedStandings = [];

        foreach ($teams as $team) {
            /** @var Standing $standing */
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

        if ($totalStrength == 0) {
            $baseHomeWinProb = 0.5;
            $baseAwayWinProb = 0.5;
        } else {
            $baseHomeWinProb = $homeStrength / $totalStrength;
            $baseAwayWinProb = $awayStrength / $totalStrength;
        }
        $drawProb = 0.25;
        $homeWinProb = $baseHomeWinProb * (1 - $drawProb);
        $awayWinProb = $baseAwayWinProb * (1 - $drawProb);

        $random = mt_rand() / mt_getrandmax();

        if ($random < $homeWinProb) {
            return ['home' => mt_rand(1, 4), 'away' => mt_rand(0, 2)];
        } elseif ($random < $homeWinProb + $awayWinProb) {
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

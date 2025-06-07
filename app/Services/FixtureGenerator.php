<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

class FixtureGenerator
{
    public function generateFixtures(): Collection
    {
        $teams = Team::all();

        if ($teams->count() !== 4) {
            throw new \Exception('Exactly 4 teams are required for the league');
        }

        GameMatch::truncate();

        $teamIds = $teams->pluck('id')->toArray();

        $fixtures = $this->generateRoundRobin($teamIds);

        foreach ($fixtures as $week => $weekFixtures) {
            foreach ($weekFixtures as $fixture) {
                GameMatch::create([
                    'home_team_id' => $fixture['home'],
                    'away_team_id' => $fixture['away'],
                    'week' => $week + 1,
                    'played' => false,
                ]);
            }
        }

        return GameMatch::with(['homeTeam', 'awayTeam'])->get();
    }

    private function generateRoundRobin(array $teams): array
    {
        $fixtures = [];
        $totalTeams = count($teams);
        $totalWeeks = ($totalTeams - 1) * 2;

        for ($week = 0; $week < $totalTeams - 1; $week++) {
            $weekFixtures = [];

            for ($i = 0; $i < $totalTeams / 2; $i++) {
                $home = ($week + $i) % ($totalTeams - 1);
                $away = ($totalTeams - 2 - $i + $week) % ($totalTeams - 1);

                if ($i == 0) {
                    $away = $totalTeams - 1;
                }

                $weekFixtures[] = [
                    'home' => $teams[$home],
                    'away' => $teams[$away],
                ];
            }

            $fixtures[] = $weekFixtures;
        }

        for ($week = 0; $week < $totalTeams - 1; $week++) {
            $weekFixtures = [];

            foreach ($fixtures[$week] as $fixture) {
                $weekFixtures[] = [
                    'home' => $fixture['away'],
                    'away' => $fixture['home'],
                ];
            }

            $fixtures[] = $weekFixtures;
        }

        return $fixtures;
    }
}

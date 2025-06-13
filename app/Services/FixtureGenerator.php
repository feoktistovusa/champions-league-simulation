<?php

namespace App\Services;

use App\Exceptions\InvalidTeamCountException;
use App\Repositories\Contracts\MatchRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FixtureGenerator
{
    /**
     * Minimum number of teams required for the league
     */
    private const MIN_TEAMS = 4;

    protected TeamRepositoryInterface $teamRepository;

    protected MatchRepositoryInterface $matchRepository;

    public function __construct(
        TeamRepositoryInterface $teamRepository,
        MatchRepositoryInterface $matchRepository
    ) {
        $this->teamRepository = $teamRepository;
        $this->matchRepository = $matchRepository;
    }

    /**
     * Generate fixtures for all teams in the league
     *
     * @throws InvalidTeamCountException if team count is not valid
     */
    public function generateFixtures(): Collection
    {
        $teams = $this->teamRepository->all();

        if ($teams->count() < self::MIN_TEAMS) {
            throw InvalidTeamCountException::insufficient(self::MIN_TEAMS, $teams->count());
        }

        if ($teams->count() % 2 !== 0) {
            throw InvalidTeamCountException::oddNumber($teams->count());
        }

        $this->matchRepository->truncate();

        $teamIds = $teams->pluck('id')->toArray();

        $fixtures = $this->generateRoundRobin($teamIds);

        $matchData = [];
        foreach ($fixtures as $week => $weekFixtures) {
            foreach ($weekFixtures as $fixture) {
                $matchData[] = [
                    'home_team_id' => $fixture['home'],
                    'away_team_id' => $fixture['away'],
                    'week' => $week + 1,
                    'played' => false,
                    'home_score' => null,
                    'away_score' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $this->matchRepository->insert($matchData);

        return $this->matchRepository->getMatchesWithTeams();
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

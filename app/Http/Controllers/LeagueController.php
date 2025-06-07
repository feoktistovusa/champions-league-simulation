<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Standing;
use App\Services\FixtureGenerator;
use App\Services\PredictionService;

class LeagueController extends Controller
{
    protected PredictionService $predictionService;

    protected FixtureGenerator $fixtureGenerator;

    public function __construct(PredictionService $predictionService, FixtureGenerator $fixtureGenerator)
    {
        $this->predictionService = $predictionService;
        $this->fixtureGenerator = $fixtureGenerator;
    }

    public function index()
    {
        return view('league.index');
    }

    public function getStandings()
    {
        $standings = Standing::with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->get();

        return response()->json($standings);
    }

    public function getCurrentWeek()
    {
        $playedMatches = GameMatch::where('played', true)->count();
        $matchesPerWeek = 2;

        $currentWeek = ceil($playedMatches / $matchesPerWeek) + 1;
        $totalWeeks = 6;

        if ($currentWeek > $totalWeeks) {
            $currentWeek = $totalWeeks;
        }

        return response()->json([
            'current_week' => $currentWeek,
            'total_weeks' => $totalWeeks,
            'all_matches_played' => $playedMatches === GameMatch::count(),
        ]);
    }

    public function getPredictions()
    {
        $currentWeek = $this->getCurrentWeek()->getData()->current_week;

        if ($currentWeek <= 4) {
            return response()->json([]);
        }

        $predictions = $this->predictionService->calculateChampionshipProbabilities();

        return response()->json($predictions);
    }

    public function resetLeague()
    {
        Standing::query()->update([
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
        ]);

        $this->fixtureGenerator->generateFixtures();

        return response()->json(['message' => 'League reset successfully']);
    }
}

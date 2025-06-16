<?php

namespace App\Http\Controllers;

use App\Http\Resources\StandingResource;
use App\Repositories\Contracts\MatchRepositoryInterface;
use App\Repositories\Contracts\StandingRepositoryInterface;
use App\Services\FixtureGenerator;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    protected PredictionService $predictionService;

    protected FixtureGenerator $fixtureGenerator;

    protected StandingRepositoryInterface $standingRepository;

    protected MatchRepositoryInterface $matchRepository;

    public function __construct(
        PredictionService $predictionService,
        FixtureGenerator $fixtureGenerator,
        StandingRepositoryInterface $standingRepository,
        MatchRepositoryInterface $matchRepository
    ) {
        $this->predictionService = $predictionService;
        $this->fixtureGenerator = $fixtureGenerator;
        $this->standingRepository = $standingRepository;
        $this->matchRepository = $matchRepository;
    }

    public function index()
    {
        return view('league.index');
    }

    public function getStandings(): JsonResponse
    {
        $standings = $this->standingRepository->getLeagueStandings();

        return response()->json([
            'data' => StandingResource::collection($standings),
        ]);
    }

    public function getCurrentWeek(): JsonResponse
    {
        $weekData = $this->matchRepository->getCurrentWeek();

        return response()->json([
            'data' => $weekData,
        ]);
    }

    public function getPredictions(): JsonResponse
    {
        $highestPlayedWeek = 0;
        for ($week = 1; $week <= 6; $week++) {
            $weekMatches = $this->matchRepository->getByWeek($week);
            $playedInWeek = $weekMatches->where('played', true)->count();

            if ($weekMatches->count() > 0 && $playedInWeek === $weekMatches->count()) {
                $highestPlayedWeek = $week;
            } else {
                break;
            }
        }

        if ($highestPlayedWeek < 4) {
            return response()->json([
                'data' => [],
            ]);
        }

        $predictions = $this->predictionService->calculateChampionshipProbabilities();

        return response()->json([
            'data' => $predictions,
        ]);
    }

    public function resetLeague(): JsonResponse
    {
        $this->standingRepository->resetAll();
        $this->fixtureGenerator->generateFixtures();

        return response()->json([
            'data' => [
                'message' => 'League reset successfully',
            ],
        ]);
    }
}

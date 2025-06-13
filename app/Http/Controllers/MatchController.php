<?php

namespace App\Http\Controllers;

use App\Exceptions\MatchNotFoundException;
use App\Http\Requests\SimulateWeekRequest;
use App\Http\Requests\UpdateMatchRequest;
use App\Http\Resources\MatchResource;
use App\Models\GameMatch;
use App\Repositories\Contracts\MatchRepositoryInterface;
use App\Services\MatchSimulator;
use App\Services\StandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    protected MatchSimulator $matchSimulator;

    protected StandingService $standingService;

    protected MatchRepositoryInterface $matchRepository;

    public function __construct(
        MatchSimulator $matchSimulator,
        StandingService $standingService,
        MatchRepositoryInterface $matchRepository
    ) {
        $this->matchSimulator = $matchSimulator;
        $this->standingService = $standingService;
        $this->matchRepository = $matchRepository;
    }

    public function getMatches(Request $request): JsonResponse
    {
        $week = $request->has('week') ? $request->input('week') : null;
        $matches = $this->matchRepository->getMatchesWithTeams($week);

        return response()->json([
            'data' => MatchResource::collection($matches),
        ]);
    }

    public function simulateWeek(SimulateWeekRequest $request): JsonResponse
    {
        $week = $request->week ?? GameMatch::getCurrentWeek()['current_week'];
        $matches = $this->matchSimulator->simulateWeek($week);

        return response()->json([
            'data' => [
                'message' => 'Week simulated successfully',
                'matches' => MatchResource::collection($matches->load(['homeTeam', 'awayTeam'])),
            ],
        ]);
    }

    public function simulateAll(): JsonResponse
    {
        $matches = $this->matchSimulator->simulateAllMatches();

        return response()->json([
            'data' => [
                'message' => 'All matches simulated successfully',
                'matches' => MatchResource::collection($matches->load(['homeTeam', 'awayTeam'])),
            ],
        ]);
    }

    public function updateMatch(UpdateMatchRequest $request, $id): JsonResponse
    {
        $match = $this->matchRepository->find($id);

        if (! $match) {
            throw MatchNotFoundException::withId((int) $id);
        }

        if ($match->played) {
            $this->standingService->resetMatchStandings($match);
        }

        $match->update([
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'played' => true,
        ]);

        $this->matchSimulator->simulateMatch($match);

        return response()->json([
            'data' => [
                'message' => 'Match updated successfully',
                'match' => new MatchResource($match->load(['homeTeam', 'awayTeam'])),
            ],
        ]);
    }
}

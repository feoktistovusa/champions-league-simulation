<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Services\MatchSimulator;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    protected MatchSimulator $matchSimulator;

    public function __construct(MatchSimulator $matchSimulator)
    {
        $this->matchSimulator = $matchSimulator;
    }

    public function getMatches(Request $request)
    {
        $query = GameMatch::with(['homeTeam', 'awayTeam']);

        if ($request->has('week')) {
            $query->where('week', $request->week);
        }

        $matches = $query->orderBy('week')->orderBy('id')->get();

        return response()->json($matches);
    }

    public function simulateWeek(Request $request)
    {
        $request->validate([
            'week' => 'required|integer|min:1|max:6',
        ]);

        $matches = $this->matchSimulator->simulateWeek($request->week);

        return response()->json([
            'message' => 'Week simulated successfully',
            'matches' => $matches->load(['homeTeam', 'awayTeam']),
        ]);
    }

    public function simulateAll()
    {
        $matches = $this->matchSimulator->simulateAllMatches();

        return response()->json([
            'message' => 'All matches simulated successfully',
            'matches' => $matches->load(['homeTeam', 'awayTeam']),
        ]);
    }

    public function updateMatch(Request $request, $id)
    {
        $request->validate([
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
        ]);

        $match = GameMatch::findOrFail($id);

        if ($match->played) {
            $this->resetMatchStandings($match);
        }

        $match->update([
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'played' => true,
        ]);

        $this->matchSimulator->simulateMatch($match);

        return response()->json([
            'message' => 'Match updated successfully',
            'match' => $match->load(['homeTeam', 'awayTeam']),
        ]);
    }

    private function resetMatchStandings(GameMatch $match): void
    {
        $homeStanding = $match->homeTeam->standing;
        $awayStanding = $match->awayTeam->standing;

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

<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create 4 teams with standings
        for ($i = 1; $i <= 4; $i++) {
            $team = Team::factory()->create(['name' => "Team $i"]);
            Standing::create(['team_id' => $team->id]);
        }
    }

    public function test_can_get_standings()
    {
        $response = $this->getJson('/api/standings');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'team_id',
                        'played',
                        'won',
                        'drawn',
                        'lost',
                        'goals_for',
                        'goals_against',
                        'goal_difference',
                        'points',
                        'team' => ['id', 'name', 'strength', 'logo'],
                    ],
                ],
            ]);
    }

    public function test_can_get_current_week()
    {
        // Create some unplayed matches
        $teams = Team::all();
        GameMatch::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'week' => 1,
            'played' => false,
        ]);

        $response = $this->getJson('/api/current-week');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'current_week',
                    'total_weeks',
                    'all_matches_played',
                ],
            ])
            ->assertJson([
                'data' => [
                    'current_week' => 1,
                    'total_weeks' => 6,
                    'all_matches_played' => false,
                ],
            ]);
    }

    public function test_can_get_matches()
    {
        // Create some matches
        $teams = Team::all();
        GameMatch::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'week' => 1,
            'played' => false,
        ]);

        $response = $this->getJson('/api/matches');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'home_team_id',
                        'away_team_id',
                        'home_score',
                        'away_score',
                        'week',
                        'played',
                        'home_team' => ['id', 'name', 'strength', 'logo'],
                        'away_team' => ['id', 'name', 'strength', 'logo'],
                    ],
                ],
            ]);
    }

    public function test_can_simulate_week()
    {
        // Create matches for week 1
        $teams = Team::all();
        GameMatch::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'week' => 1,
            'played' => false,
        ]);

        $response = $this->postJson('/api/simulate-week', ['week' => 1]);

        $response->assertStatus(200)
            ->assertJson(['data' => ['message' => 'Week simulated successfully']]);

        // Check that match was played
        $this->assertDatabaseHas('matches', [
            'week' => 1,
            'played' => true,
        ]);
    }

    public function test_can_reset_league()
    {
        $response = $this->postJson('/api/reset-league');

        $response->assertStatus(200)
            ->assertJson(['data' => ['message' => 'League reset successfully']]);

        // Check standings are reset
        $this->assertDatabaseHas('standings', [
            'played' => 0,
            'points' => 0,
        ]);
    }
}

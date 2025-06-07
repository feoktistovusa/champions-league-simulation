<?php

namespace Tests\Unit;

use App\Models\GameMatch;
use App\Models\Standing;
use App\Models\Team;
use App\Services\MatchSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchSimulatorTest extends TestCase
{
    use RefreshDatabase;

    protected MatchSimulator $simulator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simulator = new MatchSimulator;
    }

    public function test_match_simulation_generates_scores()
    {
        // Create teams
        $homeTeam = Team::factory()->create(['name' => 'Team A', 'strength' => 80]);
        $awayTeam = Team::factory()->create(['name' => 'Team B', 'strength' => 70]);

        // Create standings
        Standing::create(['team_id' => $homeTeam->id]);
        Standing::create(['team_id' => $awayTeam->id]);

        // Create match
        $match = GameMatch::create([
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'week' => 1,
            'played' => false,
        ]);

        // Simulate match
        $result = $this->simulator->simulateMatch($match);

        // Assert match was played
        $this->assertTrue($result->played);
        $this->assertNotNull($result->home_score);
        $this->assertNotNull($result->away_score);
        $this->assertGreaterThanOrEqual(0, $result->home_score);
        $this->assertGreaterThanOrEqual(0, $result->away_score);
    }

    public function test_standings_are_updated_after_match()
    {
        // Create teams
        $homeTeam = Team::factory()->create(['name' => 'Team A', 'strength' => 90]);
        $awayTeam = Team::factory()->create(['name' => 'Team B', 'strength' => 60]);

        // Create standings
        $homeStanding = Standing::create(['team_id' => $homeTeam->id]);
        $awayStanding = Standing::create(['team_id' => $awayTeam->id]);

        // Create match
        $match = GameMatch::create([
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'week' => 1,
            'played' => false,
        ]);

        // Simulate match
        $this->simulator->simulateMatch($match);

        // Refresh standings
        $homeStanding->refresh();
        $awayStanding->refresh();

        // Assert standings were updated
        $this->assertEquals(1, $homeStanding->played);
        $this->assertEquals(1, $awayStanding->played);

        // Assert points were awarded correctly
        $totalPoints = $homeStanding->points + $awayStanding->points;
        $this->assertContains($totalPoints, [2, 3]); // Either draw (2 points) or win/loss (3 points)
    }

    public function test_simulate_week_plays_all_matches()
    {
        // Create 4 teams
        $teams = [];
        for ($i = 1; $i <= 4; $i++) {
            $team = Team::factory()->create(['name' => "Team $i", 'strength' => 70 + $i * 5]);
            Standing::create(['team_id' => $team->id]);
            $teams[] = $team;
        }

        // Create week 1 matches
        GameMatch::create([
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'week' => 1,
            'played' => false,
        ]);

        GameMatch::create([
            'home_team_id' => $teams[2]->id,
            'away_team_id' => $teams[3]->id,
            'week' => 1,
            'played' => false,
        ]);

        // Simulate week 1
        $matches = $this->simulator->simulateWeek(1);

        // Assert all matches were played
        $this->assertEquals(2, $matches->count());
        $this->assertTrue($matches->every(fn ($match) => $match->played === true));
    }
}

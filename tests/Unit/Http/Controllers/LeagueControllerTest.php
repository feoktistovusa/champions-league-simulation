<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\LeagueController;
use App\Repositories\Contracts\MatchRepositoryInterface;
use App\Repositories\Contracts\StandingRepositoryInterface;
use App\Services\FixtureGenerator;
use App\Services\PredictionService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class LeagueControllerTest extends TestCase
{
    protected LeagueController $controller;

    protected $mockPredictionService;

    protected $mockFixtureGenerator;

    protected $mockStandingRepository;

    protected $mockMatchRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockPredictionService = Mockery::mock(PredictionService::class);
        $this->mockFixtureGenerator = Mockery::mock(FixtureGenerator::class);
        $this->mockStandingRepository = Mockery::mock(StandingRepositoryInterface::class);
        $this->mockMatchRepository = Mockery::mock(MatchRepositoryInterface::class);

        $this->controller = new LeagueController(
            $this->mockPredictionService,
            $this->mockFixtureGenerator,
            $this->mockStandingRepository,
            $this->mockMatchRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_standings()
    {
        $team1 = (object) [
            'id' => 1,
            'name' => 'Team 1',
            'strength' => 80,
            'logo' => 'logo1.png',
        ];

        $team2 = (object) [
            'id' => 2,
            'name' => 'Team 2',
            'strength' => 75,
            'logo' => 'logo2.png',
        ];

        $standing1 = (object) [
            'id' => 1,
            'team_id' => 1,
            'team' => $team1,
            'played' => 5,
            'won' => 3,
            'drawn' => 1,
            'lost' => 1,
            'goals_for' => 10,
            'goals_against' => 7,
            'goal_difference' => 3,
            'points' => 10,
        ];

        $standing2 = (object) [
            'id' => 2,
            'team_id' => 2,
            'team' => $team2,
            'played' => 5,
            'won' => 2,
            'drawn' => 2,
            'lost' => 1,
            'goals_for' => 8,
            'goals_against' => 6,
            'goal_difference' => 2,
            'points' => 8,
        ];

        $standings = new Collection([$standing1, $standing2]);

        $this->mockStandingRepository->shouldReceive('getLeagueStandings')
            ->once()
            ->andReturn($standings);

        $response = $this->controller->getStandings();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
    }

    public function test_get_current_week()
    {
        $weekData = [
            'current_week' => 3,
            'total_weeks' => 6,
            'all_matches_played' => false,
        ];

        $this->mockMatchRepository->shouldReceive('getCurrentWeek')
            ->once()
            ->andReturn($weekData);

        $response = $this->controller->getCurrentWeek();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($weekData, $data['data']);
    }

    public function test_get_predictions_before_week_4_returns_empty()
    {
        $weekData = [
            'current_week' => 3,
            'total_weeks' => 6,
            'all_matches_played' => false,
        ];

        $this->mockMatchRepository->shouldReceive('getCurrentWeek')
            ->once()
            ->andReturn($weekData);

        $response = $this->controller->getPredictions();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEmpty($data['data']);
    }

    public function test_get_predictions_after_week_4()
    {
        $weekData = [
            'current_week' => 5,
            'total_weeks' => 6,
            'all_matches_played' => false,
        ];

        $predictions = [
            ['team_id' => 1, 'probability' => 0.45],
            ['team_id' => 2, 'probability' => 0.35],
        ];

        $this->mockMatchRepository->shouldReceive('getCurrentWeek')
            ->once()
            ->andReturn($weekData);

        $this->mockPredictionService->shouldReceive('calculateChampionshipProbabilities')
            ->once()
            ->andReturn($predictions);

        $response = $this->controller->getPredictions();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($predictions, $data['data']);
    }

    public function test_reset_league()
    {
        $this->mockStandingRepository->shouldReceive('resetAll')
            ->once()
            ->andReturn(4);

        $this->mockFixtureGenerator->shouldReceive('generateFixtures')
            ->once()
            ->andReturn(new Collection);

        $response = $this->controller->resetLeague();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('League reset successfully', $data['data']['message']);
    }
}

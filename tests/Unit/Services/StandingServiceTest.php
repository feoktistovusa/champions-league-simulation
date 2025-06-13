<?php

namespace Tests\Unit\Services;

use App\Models\GameMatch;
use App\Models\Standing;
use App\Models\Team;
use App\Services\StandingService;
use Mockery;
use PHPUnit\Framework\TestCase;

class StandingServiceTest extends TestCase
{
    protected StandingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StandingService;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_reset_match_standings_with_home_win()
    {
        $homeTeam = Mockery::mock(Team::class);
        $awayTeam = Mockery::mock(Team::class);
        $homeStanding = Mockery::mock(Standing::class)->makePartial();
        $awayStanding = Mockery::mock(Standing::class)->makePartial();

        $homeStanding->played = 5;
        $homeStanding->won = 3;
        $homeStanding->goals_for = 10;
        $homeStanding->goals_against = 5;
        $homeStanding->points = 9;

        $awayStanding->played = 5;
        $awayStanding->lost = 2;
        $awayStanding->goals_for = 7;
        $awayStanding->goals_against = 8;
        $awayStanding->points = 6;

        $match = Mockery::mock(GameMatch::class);
        $match->shouldReceive('getAttribute')->with('played')->andReturn(true);
        $match->shouldReceive('getAttribute')->with('home_score')->andReturn(2);
        $match->shouldReceive('getAttribute')->with('away_score')->andReturn(1);
        $match->shouldReceive('getAttribute')->with('homeTeam')->andReturn($homeTeam);
        $match->shouldReceive('getAttribute')->with('awayTeam')->andReturn($awayTeam);

        $homeTeam->shouldReceive('getAttribute')->with('standing')->andReturn($homeStanding);
        $awayTeam->shouldReceive('getAttribute')->with('standing')->andReturn($awayStanding);

        $homeStanding->shouldReceive('save')->once();
        $awayStanding->shouldReceive('save')->once();

        $this->service->resetMatchStandings($match);

        $this->assertEquals(4, $homeStanding->played);
        $this->assertEquals(2, $homeStanding->won);
        $this->assertEquals(8, $homeStanding->goals_for);
        $this->assertEquals(4, $homeStanding->goals_against);
        $this->assertEquals(6, $homeStanding->points);

        $this->assertEquals(4, $awayStanding->played);
        $this->assertEquals(1, $awayStanding->lost);
        $this->assertEquals(6, $awayStanding->goals_for);
        $this->assertEquals(6, $awayStanding->goals_against);
        $this->assertEquals(6, $awayStanding->points);
    }

    public function test_reset_match_standings_with_draw()
    {
        $homeTeam = Mockery::mock(Team::class);
        $awayTeam = Mockery::mock(Team::class);
        $homeStanding = Mockery::mock(Standing::class)->makePartial();
        $awayStanding = Mockery::mock(Standing::class)->makePartial();

        $homeStanding->played = 5;
        $homeStanding->drawn = 2;
        $homeStanding->goals_for = 10;
        $homeStanding->goals_against = 10;
        $homeStanding->points = 8;

        $awayStanding->played = 5;
        $awayStanding->drawn = 2;
        $awayStanding->goals_for = 8;
        $awayStanding->goals_against = 8;
        $awayStanding->points = 8;

        $match = Mockery::mock(GameMatch::class);
        $match->shouldReceive('getAttribute')->with('played')->andReturn(true);
        $match->shouldReceive('getAttribute')->with('home_score')->andReturn(1);
        $match->shouldReceive('getAttribute')->with('away_score')->andReturn(1);
        $match->shouldReceive('getAttribute')->with('homeTeam')->andReturn($homeTeam);
        $match->shouldReceive('getAttribute')->with('awayTeam')->andReturn($awayTeam);

        $homeTeam->shouldReceive('getAttribute')->with('standing')->andReturn($homeStanding);
        $awayTeam->shouldReceive('getAttribute')->with('standing')->andReturn($awayStanding);

        $homeStanding->shouldReceive('save')->once();
        $awayStanding->shouldReceive('save')->once();

        $this->service->resetMatchStandings($match);

        $this->assertEquals(4, $homeStanding->played);
        $this->assertEquals(1, $homeStanding->drawn);
        $this->assertEquals(9, $homeStanding->goals_for);
        $this->assertEquals(9, $homeStanding->goals_against);
        $this->assertEquals(7, $homeStanding->points);

        $this->assertEquals(4, $awayStanding->played);
        $this->assertEquals(1, $awayStanding->drawn);
        $this->assertEquals(7, $awayStanding->goals_for);
        $this->assertEquals(7, $awayStanding->goals_against);
        $this->assertEquals(7, $awayStanding->points);
    }

    public function test_does_not_reset_if_match_not_played()
    {
        $match = Mockery::mock(GameMatch::class);
        $match->shouldReceive('getAttribute')->with('played')->andReturn(false);

        $match->shouldNotReceive('getAttribute')->with('homeTeam');
        $match->shouldNotReceive('getAttribute')->with('awayTeam');

        $this->service->resetMatchStandings($match);
    }
}

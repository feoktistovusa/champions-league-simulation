<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\Contracts\MatchRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Services\FixtureGenerator;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class FixtureGeneratorTest extends TestCase
{
    protected FixtureGenerator $service;

    protected $mockTeamRepository;

    protected $mockMatchRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTeamRepository = Mockery::mock(TeamRepositoryInterface::class);
        $this->mockMatchRepository = Mockery::mock(MatchRepositoryInterface::class);

        $this->service = new FixtureGenerator(
            $this->mockTeamRepository,
            $this->mockMatchRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generate_fixtures_with_valid_team_count()
    {
        // Create mock teams
        $teams = new Collection([
            (object) ['id' => 1, 'name' => 'Team 1'],
            (object) ['id' => 2, 'name' => 'Team 2'],
            (object) ['id' => 3, 'name' => 'Team 3'],
            (object) ['id' => 4, 'name' => 'Team 4'],
        ]);

        $expectedMatches = new Collection;

        $this->mockTeamRepository->shouldReceive('all')
            ->once()
            ->andReturn($teams);

        $this->mockMatchRepository->shouldReceive('truncate')
            ->once();

        $this->mockMatchRepository->shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($data) {
                $this->assertCount(12, $data);

                foreach ($data as $match) {
                    $this->assertArrayHasKey('home_team_id', $match);
                    $this->assertArrayHasKey('away_team_id', $match);
                    $this->assertArrayHasKey('week', $match);
                    $this->assertArrayHasKey('played', $match);
                    $this->assertFalse($match['played']);
                }

                return true;
            }))
            ->andReturn(true);

        $this->mockMatchRepository->shouldReceive('getMatchesWithTeams')
            ->once()
            ->andReturn($expectedMatches);

        $result = $this->service->generateFixtures();

        $this->assertSame($expectedMatches, $result);
    }

    public function test_generate_fixtures_throws_exception_for_insufficient_teams()
    {
        $teams = new Collection([
            (object) ['id' => 1, 'name' => 'Team 1'],
            (object) ['id' => 2, 'name' => 'Team 2'],
        ]);

        $this->mockTeamRepository->shouldReceive('all')
            ->once()
            ->andReturn($teams);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('At least 4 teams are required for the league, 2 found');

        $this->service->generateFixtures();
    }

    public function test_generate_fixtures_throws_exception_for_odd_team_count()
    {
        $teams = new Collection([
            (object) ['id' => 1, 'name' => 'Team 1'],
            (object) ['id' => 2, 'name' => 'Team 2'],
            (object) ['id' => 3, 'name' => 'Team 3'],
            (object) ['id' => 4, 'name' => 'Team 4'],
            (object) ['id' => 5, 'name' => 'Team 5'],
        ]);

        $this->mockTeamRepository->shouldReceive('all')
            ->once()
            ->andReturn($teams);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('An even number of teams is required for round-robin fixtures');

        $this->service->generateFixtures();
    }

    public function test_generate_fixtures_with_six_teams()
    {
        $teams = new Collection([
            (object) ['id' => 1, 'name' => 'Team 1'],
            (object) ['id' => 2, 'name' => 'Team 2'],
            (object) ['id' => 3, 'name' => 'Team 3'],
            (object) ['id' => 4, 'name' => 'Team 4'],
            (object) ['id' => 5, 'name' => 'Team 5'],
            (object) ['id' => 6, 'name' => 'Team 6'],
        ]);

        $expectedMatches = new Collection;

        $this->mockTeamRepository->shouldReceive('all')
            ->once()
            ->andReturn($teams);

        $this->mockMatchRepository->shouldReceive('truncate')
            ->once();

        $this->mockMatchRepository->shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($data) {
                $this->assertCount(30, $data);

                return true;
            }))
            ->andReturn(true);

        $this->mockMatchRepository->shouldReceive('getMatchesWithTeams')
            ->once()
            ->andReturn($expectedMatches);

        $result = $this->service->generateFixtures();

        $this->assertSame($expectedMatches, $result);
    }
}

<?php

namespace Tests\Unit\Repositories;

use App\Models\Standing;
use App\Repositories\Eloquent\StandingRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class StandingRepositoryTest extends TestCase
{
    protected StandingRepository $repository;

    protected $mockModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockModel = Mockery::mock(Standing::class);
        $this->repository = new StandingRepository($this->mockModel);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_league_standings()
    {
        $expectedCollection = new Collection;

        $this->mockModel->shouldReceive('getLeagueStandings')
            ->once()
            ->andReturn($expectedCollection);

        $result = $this->repository->getLeagueStandings();

        $this->assertSame($expectedCollection, $result);
    }

    public function test_reset_all()
    {
        $expectedCount = 4;

        $this->mockModel->shouldReceive('resetAll')
            ->once()
            ->andReturn($expectedCount);

        $result = $this->repository->resetAll();

        $this->assertEquals($expectedCount, $result);
    }

    public function test_find_by_team_id()
    {
        $teamId = 1;
        $mockStanding = Mockery::mock(Standing::class);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('first')
            ->once()
            ->andReturn($mockStanding);

        $this->mockModel->shouldReceive('where')
            ->with('team_id', $teamId)
            ->once()
            ->andReturn($builder);

        $result = $this->repository->findByTeamId($teamId);

        $this->assertSame($mockStanding, $result);
    }

    public function test_update()
    {
        $id = 1;
        $data = ['points' => 10, 'won' => 3];

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('update')
            ->with($data)
            ->once()
            ->andReturn(1);

        $this->mockModel->shouldReceive('where')
            ->with('id', $id)
            ->once()
            ->andReturn($builder);

        $result = $this->repository->update($id, $data);

        $this->assertTrue($result);
    }

    public function test_update_returns_false_when_no_rows_affected()
    {
        $id = 999;
        $data = ['points' => 10];

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('update')
            ->with($data)
            ->once()
            ->andReturn(0);

        $this->mockModel->shouldReceive('where')
            ->with('id', $id)
            ->once()
            ->andReturn($builder);

        $result = $this->repository->update($id, $data);

        $this->assertFalse($result);
    }
}

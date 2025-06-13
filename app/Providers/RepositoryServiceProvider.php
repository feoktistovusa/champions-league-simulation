<?php

namespace App\Providers;

use App\Repositories\Contracts\MatchRepositoryInterface;
use App\Repositories\Contracts\StandingRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Repositories\Eloquent\MatchRepository;
use App\Repositories\Eloquent\StandingRepository;
use App\Repositories\Eloquent\TeamRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(StandingRepositoryInterface::class, StandingRepository::class);
        $this->app->bind(MatchRepositoryInterface::class, MatchRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

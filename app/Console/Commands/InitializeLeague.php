<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FixtureGenerator;
use Illuminate\Console\Command;

class InitializeLeague extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'league:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the league with fixtures';

    /**
     * FixtureGenerator service
     */
    protected FixtureGenerator $fixtureGenerator;

    /**
     * Create a new command instance
     */
    public function __construct(FixtureGenerator $fixtureGenerator)
    {
        parent::__construct();
        $this->fixtureGenerator = $fixtureGenerator;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Initializing league fixtures...');

        $fixtures = $this->fixtureGenerator->generateFixtures();

        $this->info('Generated '.$fixtures->count().' fixtures successfully!');

        return Command::SUCCESS;
    }
}

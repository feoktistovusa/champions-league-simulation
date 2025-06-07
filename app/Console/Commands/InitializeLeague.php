<?php

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
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing league fixtures...');

        $fixtureGenerator = new FixtureGenerator;
        $fixtures = $fixtureGenerator->generateFixtures();

        $this->info('Generated '.$fixtures->count().' fixtures successfully!');

        return Command::SUCCESS;
    }
}

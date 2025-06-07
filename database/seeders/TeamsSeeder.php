<?php

namespace Database\Seeders;

use App\Models\Standing;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            ['name' => 'Manchester City', 'strength' => 90],
            ['name' => 'Bayern Munich', 'strength' => 85],
            ['name' => 'Real Madrid', 'strength' => 88],
            ['name' => 'Barcelona', 'strength' => 82],
        ];

        foreach ($teams as $teamData) {
            $team = Team::create($teamData);
            Standing::create(['team_id' => $team->id]);
        }
    }
}

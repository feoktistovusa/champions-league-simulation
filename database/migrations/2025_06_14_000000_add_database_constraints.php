<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE matches ADD CONSTRAINT check_different_teams CHECK (home_team_id != away_team_id)');

            DB::statement('ALTER TABLE matches ADD CONSTRAINT check_home_score_limit CHECK (home_score >= 0 AND home_score <= 20)');
            DB::statement('ALTER TABLE matches ADD CONSTRAINT check_away_score_limit CHECK (away_score >= 0 AND away_score <= 20)');

            DB::statement('ALTER TABLE standings ADD CONSTRAINT check_played_non_negative CHECK (played >= 0)');
            DB::statement('ALTER TABLE standings ADD CONSTRAINT check_won_non_negative CHECK (won >= 0)');
            DB::statement('ALTER TABLE standings ADD CONSTRAINT check_drawn_non_negative CHECK (drawn >= 0)');
            DB::statement('ALTER TABLE standings ADD CONSTRAINT check_lost_non_negative CHECK (lost >= 0)');
            DB::statement('ALTER TABLE standings ADD CONSTRAINT check_goals_for_non_negative CHECK (goals_for >= 0)');
            DB::statement('ALTER TABLE standings ADD CONSTRAINT check_goals_against_non_negative CHECK (goals_against >= 0)');
            DB::statement('ALTER TABLE standings ADD CONSTRAINT check_points_non_negative CHECK (points >= 0)');

            DB::statement('ALTER TABLE teams ADD CONSTRAINT check_strength_positive CHECK (strength > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE matches DROP CONSTRAINT IF EXISTS check_different_teams');
            DB::statement('ALTER TABLE matches DROP CONSTRAINT IF EXISTS check_home_score_limit');
            DB::statement('ALTER TABLE matches DROP CONSTRAINT IF EXISTS check_away_score_limit');

            DB::statement('ALTER TABLE standings DROP CONSTRAINT IF EXISTS check_played_non_negative');
            DB::statement('ALTER TABLE standings DROP CONSTRAINT IF EXISTS check_won_non_negative');
            DB::statement('ALTER TABLE standings DROP CONSTRAINT IF EXISTS check_drawn_non_negative');
            DB::statement('ALTER TABLE standings DROP CONSTRAINT IF EXISTS check_lost_non_negative');
            DB::statement('ALTER TABLE standings DROP CONSTRAINT IF EXISTS check_goals_for_non_negative');
            DB::statement('ALTER TABLE standings DROP CONSTRAINT IF EXISTS check_goals_against_non_negative');
            DB::statement('ALTER TABLE standings DROP CONSTRAINT IF EXISTS check_points_non_negative');

            DB::statement('ALTER TABLE teams DROP CONSTRAINT IF EXISTS check_strength_positive');
        }
    }
};

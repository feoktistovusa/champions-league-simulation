<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->index('week');
            $table->index('played');
            $table->index(['week', 'played']);
            $table->index('home_team_id');
            $table->index('away_team_id');
        });

        Schema::table('standings', function (Blueprint $table) {
            $table->index('team_id');
            $table->index(['points', 'goal_difference', 'goals_for']);
            $table->index('points');
            $table->index('goal_difference');
            $table->index('goals_for');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->index('name');
            $table->index('strength');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex(['week']);
            $table->dropIndex(['played']);
            $table->dropIndex(['week', 'played']);
            $table->dropIndex(['home_team_id']);
            $table->dropIndex(['away_team_id']);
        });

        Schema::table('standings', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropIndex(['points', 'goal_difference', 'goals_for']);
            $table->dropIndex(['points']);
            $table->dropIndex(['goal_difference']);
            $table->dropIndex(['goals_for']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['strength']);
        });
    }
};

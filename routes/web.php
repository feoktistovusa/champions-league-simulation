<?php

use App\Http\Controllers\LeagueController;
use App\Http\Controllers\MatchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeagueController::class, 'index']);

// API Routes
Route::prefix('api')->group(function () {
    // League routes
    Route::get('/standings', [LeagueController::class, 'getStandings']);
    Route::get('/current-week', [LeagueController::class, 'getCurrentWeek']);
    Route::get('/predictions', [LeagueController::class, 'getPredictions']);
    Route::post('/reset-league', [LeagueController::class, 'resetLeague']);

    // Match routes
    Route::get('/matches', [MatchController::class, 'getMatches']);
    Route::post('/simulate-week', [MatchController::class, 'simulateWeek']);
    Route::post('/simulate-all', [MatchController::class, 'simulateAll']);
    Route::put('/matches/{id}', [MatchController::class, 'updateMatch']);
});

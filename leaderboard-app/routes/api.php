<?php

// use App\Http\Controllers\Api\PlayerController;
// use App\Http\Controllers\Api\ScoreController;
// use App\Http\Controllers\Api\TeamController;
// use Illuminate\Support\Facades\Route;

// Route::get('/test', function () {
//     return response()->json(['message' => 'API is working!']);
// });

// Route::prefix('v1')->group(function () {
//     // Player routes
//     Route::get('/players', [PlayerController::class, 'index']);
//     Route::post('/players', [PlayerController::class, 'store']);
//     Route::get('/players/{player}', [PlayerController::class, 'show']);
//     Route::delete('/players/{player}', [PlayerController::class, 'destroy']);
//     Route::apiResource('players', PlayerController::class);
//     Route::get('players/{player}/history', [PlayerController::class, 'history']);
    

//     // Teams routes
//     Route::apiResource('teams', TeamController::class);
//     Route::get('teams/{team}/leaderboard', [TeamController::class, 'leaderboard']);
    

//     // Score routes
//     Route::post('/players/{player}/score', [ScoreController::class, 'update']);
//     Route::post('players/{player}/score', [ScoreController::class, 'update']);
//     Route::post('players/{player}/score/reset', [ScoreController::class, 'reset']);
// });


use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    
    // Teams routes
    Route::apiResource('teams', TeamController::class);
    Route::get('teams/{team}/leaderboard', [TeamController::class, 'leaderboard']);
    
    // Players routes
    Route::apiResource('players', PlayerController::class);
    Route::get('players/{player}/history', [PlayerController::class, 'history']);
    
    // Score routes
    Route::post('players/{player}/score', [ScoreController::class, 'update']);
    Route::post('players/{player}/score/reset', [ScoreController::class, 'reset']);
});
<?php

namespace App\Http\Controllers\Api;

use App\Events\ScoreUpdated;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\ScoreHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
{
    // POST /api/v1/players/{player}/score - Update player score
    public function update(Request $request, Player $player): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer',
            'reason' => 'nullable|string|max:255',
            'updated_by' => 'nullable|string|max:255',
        ]);

        $pointsAdded = $validated['points'];
        $oldScore = $player->score;
        
        DB::transaction(function () use ($player, $pointsAdded, $oldScore, $validated) {
            // Update player score
            $player->increment('score', $pointsAdded);
            $player->refresh();

            // Create score history record
            ScoreHistory::create([
                'player_id' => $player->id,
                'old_score' => $oldScore,
                'new_score' => $player->score,
                'points_changed' => $pointsAdded,
                'reason' => $validated['reason'] ?? 'Manual update',
                'updated_by' => $validated['updated_by'] ?? 'System',
            ]);
        });

        // Broadcast the score update
        broadcast(new ScoreUpdated($player, $pointsAdded));

        return response()->json([
            'success' => true,
            'data' => $player->load('team'),
            'message' => 'Score updated successfully',
        ]);
    }

    // POST /api/v1/players/{player}/score/reset - Reset player score
    public function reset(Player $player): JsonResponse
    {
        $oldScore = $player->score;

        DB::transaction(function () use ($player, $oldScore) {
            // Create history record
            ScoreHistory::create([
                'player_id' => $player->id,
                'old_score' => $oldScore,
                'new_score' => 0,
                'points_changed' => -$oldScore,
                'reason' => 'Score reset',
                'updated_by' => 'System',
            ]);

            // Reset score
            $player->update(['score' => 0]);
        });

        // Broadcast the reset
        broadcast(new ScoreUpdated($player, -$oldScore));

        return response()->json([
            'success' => true,
            'data' => $player,
            'message' => 'Score reset successfully',
        ]);
    }
}
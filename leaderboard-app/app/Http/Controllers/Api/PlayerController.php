<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    // GET /api/v1/players - Get all players
    public function index(Request $request): JsonResponse
    {
        $query = Player::with('team');

        // Filter by team if provided
        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $players = $query->orderBy('score', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $players,
        ]);
    }

    // GET /api/v1/players/{player} - Get single player
    public function show(Player $player): JsonResponse
    {
        $player->load(['team', 'scoreHistories' => function($query) {
            $query->latest()->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $player,
        ]);
    }

    // POST /api/v1/players - Create new player
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'team_id' => 'nullable|exists:teams,id',
            'score' => 'sometimes|integer|min:0',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $player = Player::create($validated);
        $player->load('team');

        return response()->json([
            'success' => true,
            'data' => $player,
            'message' => 'Player created successfully',
        ], 201);
    }

    // PUT /api/v1/players/{player} - Update player
    public function update(Request $request, Player $player): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'team_id' => 'nullable|exists:teams,id',
            'score' => 'sometimes|integer|min:0',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($player->avatar) {
                Storage::disk('public')->delete($player->avatar);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $player->update($validated);
        $player->load('team');

        return response()->json([
            'success' => true,
            'data' => $player,
            'message' => 'Player updated successfully',
        ]);
    }

    // DELETE /api/v1/players/{player} - Delete player
    public function destroy(Player $player): JsonResponse
    {
        // Delete avatar if exists
        if ($player->avatar) {
            Storage::disk('public')->delete($player->avatar);
        }

        $player->delete();

        return response()->json([
            'success' => true,
            'message' => 'Player deleted successfully',
        ]);
    }

    // GET /api/v1/players/{player}/history - Get player's score history
    public function history(Player $player): JsonResponse
    {
        $history = $player->scoreHistories()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
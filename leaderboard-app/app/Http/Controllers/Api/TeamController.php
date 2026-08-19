<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    // GET /api/v1/teams - Get all teams
    public function index(): JsonResponse
    {
        $teams = Team::withCount('players')
            ->with(['players' => function($query) {
                $query->select('id', 'name', 'avatar', 'score', 'team_id');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $teams,
        ]);
    }

    // GET /api/v1/teams/{team} - Get single team
    public function show(Team $team): JsonResponse
    {
        $team->load(['players' => function($query) {
            $query->orderBy('score', 'desc');
        }]);

        return response()->json([
            'success' => true,
            'data' => $team,
        ]);
    }

    // POST /api/v1/teams - Create new team
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name',
            'logo' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
        ]);

        $team = Team::create($validated);

        return response()->json([
            'success' => true,
            'data' => $team,
            'message' => 'Team created successfully',
        ], 201);
    }

    // PUT /api/v1/teams/{team} - Update team
    public function update(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'logo' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
        ]);

        $team->update($validated);

        return response()->json([
            'success' => true,
            'data' => $team,
            'message' => 'Team updated successfully',
        ]);
    }

    // DELETE /api/v1/teams/{team} - Delete team
    public function destroy(Team $team): JsonResponse
    {
        // Check if team has players
        if ($team->players()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete team with players. Remove players first.',
            ], 400);
        }

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully',
        ]);
    }

    // GET /api/v1/teams/{team}/leaderboard - Get team leaderboard
    public function leaderboard(Team $team): JsonResponse
    {
        $players = $team->players()
            ->orderBy('score', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'team' => $team,
                'players' => $players,
            ],
        ]);
    }
}
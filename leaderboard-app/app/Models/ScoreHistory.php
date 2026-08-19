<?php
// app/Models/ScoreHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class ScoreHistory extends Model
{
    use HasFactory;

    // Disable updated_at (we only need created_at)
    const UPDATED_AT = null;

    protected $fillable = [
        'player_id',
        'old_score',
        'new_score',
        'points_changed',
        'reason',
        'updated_by',
    ];

    protected $casts = [
        'old_score' => 'integer',
        'new_score' => 'integer',
        'points_changed' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationship: A history entry belongs to a player
    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
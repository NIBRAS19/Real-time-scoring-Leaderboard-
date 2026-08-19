<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'avatar',
        'team_id',
        'score',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    // Relationship: A player belongs to a team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Relationship: A player has many score histories
    public function scoreHistories()
    {
        return $this->hasMany(ScoreHistory::class)->orderBy('created_at', 'desc');
    }

    // Get avatar URL (handle both uploads and default avatars)
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }
        
        // Return default avatar based on first letter of name
        $letter = strtoupper(substr($this->name, 0, 1));
        return "https://ui-avatars.com/api/?name={$this->name}&background=667eea&color=fff&size=200&font-size=0.5&bold=true";
    }

    // Append avatar URL to JSON
    protected $appends = ['avatar_url'];

    // Include team and history count in array
    public function toArray()
    {
        $array = parent::toArray();
        $array['team'] = $this->team;
        $array['history_count'] = $this->scoreHistories()->count();
        return $array;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'color',
        'description',
    ];

    // Relationship: A team has many players
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    // Computed attribute: Total team score
    public function getTotalScoreAttribute()
    {
        return $this->players()->sum('score');
    }

    // Computed attribute: Average team score
    public function getAverageScoreAttribute()
    {
        $count = $this->players()->count();
        return $count > 0 ? round($this->total_score / $count) : 0;
    }

    // Append computed attributes to JSON
    protected $appends = ['total_score', 'average_score'];

    // Include player count when converting to array
    public function toArray()
    {
        $array = parent::toArray();
        $array['players_count'] = $this->players()->count();
        return $array;
    }
}
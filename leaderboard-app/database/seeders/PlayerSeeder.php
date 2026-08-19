<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        Player::create(['name' => 'Alice', 'score' => 100]);
        Player::create(['name' => 'Bob', 'score' => 85]);
        Player::create(['name' => 'Charlie', 'score' => 120]);
        Player::create(['name' => 'Diana', 'score' => 95]);
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->integer('old_score');
            $table->integer('new_score');
            $table->integer('points_changed');
            $table->string('reason')->nullable();
            $table->string('updated_by')->nullable(); // Who made the change
            $table->timestamp('created_at');
            $table->index(['player_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_histories');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->string('session_id')->nullable(); // Track by session for now
            $table->integer('usage_count')->default(0); // How many times used
            $table->timestamps();
            
            // Prevent duplicate pairs per session
            $table->unique(['from_currency', 'to_currency', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_pairs');
    }
};
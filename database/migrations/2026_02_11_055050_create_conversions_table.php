<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->decimal('amount', 20, 2); // Amount to convert
            $table->decimal('rate', 20, 8); // Rate used for conversion
            $table->decimal('result', 20, 2); // Converted amount
            $table->string('ip_address', 45)->nullable(); // Track user (optional)
            $table->timestamps();
            
            // Index for history queries
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversions');
    }
};
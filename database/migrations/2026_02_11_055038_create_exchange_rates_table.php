<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3); // USD
            $table->string('target_currency', 3); // EUR
            $table->decimal('rate', 20, 8); // Exchange rate with high precision
            $table->timestamp('fetched_at'); // When this rate was fetched
            $table->timestamps();
            
            // Composite index for faster lookups
            $table->index(['base_currency', 'target_currency', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
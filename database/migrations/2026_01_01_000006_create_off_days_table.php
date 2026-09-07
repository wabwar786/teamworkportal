<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Off-day overrides. Sat/Sun are off by default in code; this table
 *  stores exceptions (owner marked a weekend as working, etc). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('off_days', function (Blueprint $table) {
            $table->id();
            $table->date('day')->unique();
            $table->boolean('forced_working')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('off_days');
    }
};

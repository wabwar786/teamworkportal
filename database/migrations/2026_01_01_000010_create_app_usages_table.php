<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Per-day active-app minutes reported by the agent. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->string('app_name');
            $table->unsignedInteger('minutes')->default(0);
            $table->timestamps();

            $table->unique(['device_id', 'usage_date', 'app_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_usages');
    }
};

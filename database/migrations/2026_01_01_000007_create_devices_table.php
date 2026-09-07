<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A monitored PC running the agent, tied to an employee. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pc_name')->nullable();
            $table->string('os')->nullable();
            $table->string('ip')->nullable();
            $table->string('agent_version')->nullable();
            $table->string('enroll_token')->unique();   // agent uses this to authenticate
            $table->timestamp('last_seen')->nullable();
            $table->boolean('online')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};

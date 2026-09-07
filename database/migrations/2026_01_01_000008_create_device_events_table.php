<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** File/USB/app events reported by the agent. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['copy', 'move', 'delete', 'usb', 'app'])->index();
            $table->text('path')->nullable();
            $table->text('destination')->nullable();
            $table->string('size')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('happened_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_events');
    }
};

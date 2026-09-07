<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One support conversation with captured customer context. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('visitor')->nullable();
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->enum('channel', ['web', 'desktop'])->default('web');
            $table->string('city')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('page')->nullable();
            $table->string('app_label')->nullable();
            $table->string('ip')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('last_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

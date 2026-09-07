<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Sites/software to block, targeted at one device or all. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocklist', function (Blueprint $table) {
            $table->id();
            $table->enum('scope', ['site', 'app']);
            $table->string('value');
            $table->string('target')->default('all');   // 'all' or device id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocklist');
    }
};

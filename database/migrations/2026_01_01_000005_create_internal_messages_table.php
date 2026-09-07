<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal chat: owner <-> employee and employee <-> employee.
 * Polymorphic-ish: from/to are stored as "type:id" (owner:1 / emp:3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_messages', function (Blueprint $table) {
            $table->id();
            $table->string('from_ref');   // e.g. "owner:1" or "emp:3"
            $table->string('to_ref');
            $table->text('body')->nullable();
            $table->longText('image')->nullable();   // base64 data URL (attachments)
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['to_ref', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_messages');
    }
};

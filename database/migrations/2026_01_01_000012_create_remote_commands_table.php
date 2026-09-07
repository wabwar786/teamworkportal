<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safe, whitelisted remote actions only (lock/restart/message/sync...).
 * The agent pulls queued commands and reports back. There is NO
 * arbitrary-command field by design.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remote_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('action', [
                'lock', 'message', 'restart', 'shutdown', 'logoff',
                'folder_sync', 'screenshot', 'blocklist_push', 'app_close', 'agent_update',
            ]);
            $table->json('args')->nullable();          // e.g. {"text":"..."} for message
            $table->enum('status', ['queued', 'sent', 'done', 'failed'])->default('queued');
            $table->foreignId('issued_by')->nullable()->constrained('owners')->nullOnDelete();
            $table->timestamp('done_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remote_commands');
    }
};

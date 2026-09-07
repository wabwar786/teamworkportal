<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employees = team members (developer, designer, marketing, sales...).
 * They do NOT log in with a password — they pick their name on the
 * start screen. `archived` is how "delete" works: the row and all its
 * logs are kept, the person is just hidden from the active portal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();               // used on the start screen
            $table->string('role')->default('Developer');   // Developer/Designer/Marketing/Sales/Other
            $table->foreignId('head_id')->nullable()->constrained('owners')->nullOndelete();
            $table->json('blocks')->nullable();             // which input blocks they see
            $table->text('shared_note')->nullable();        // owner -> this employee only
            $table->string('pin', 8)->nullable();           // optional light PIN
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->boolean('active')->default(true);       // false = suspended (login blocked)
            $table->boolean('archived')->default(false);    // true = deleted (log preserved)
            $table->timestamps();

            $table->index(['archived', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

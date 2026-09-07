<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One work_log row per employee per day. Kept even after the employee
 * is archived — this is the preserved history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->text('done')->nullable();       // completed today
            $table->text('upload')->nullable();     // delivered/uploaded today
            $table->text('pending')->nullable();    // still pending
            $table->text('critical')->nullable();   // critical work done today
            $table->text('saved')->nullable();      // passwords/links/notes
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};

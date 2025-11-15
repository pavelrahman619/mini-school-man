<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['Present', 'Absent', 'Late']);
            $table->text('note')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            // Unique constraint to prevent duplicate attendance for same student on same date
            $table->unique(['student_id', 'date']);

            // Indexes for frequently queried columns
            $table->index('date');
            $table->index('status');
            $table->index(['student_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

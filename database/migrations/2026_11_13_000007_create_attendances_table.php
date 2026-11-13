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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('training_schedule_id')->nullable()->constrained('training_schedules')->nullOnDelete();
            $table->foreignId('duty_assignment_id')->nullable()->constrained('duty_assignments')->nullOnDelete();
            $table->date('date');
            $table->time('time');
            $table->string('status')->default('Hadir'); // Hadir, Izin, Sakit, Alpa
            $table->text('notes')->nullable();
            $table->decimal('session_rate', 12, 2)->default(50000.00);
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['date', 'status']);
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


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
        Schema::create('workload_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('activity_name'); // e.g., "Continuous patient monitoring", "Wound dressing"
            $table->enum('activity_type', ['health_service', 'support', 'additional']); // Core WISN classifications
            $table->decimal('time_standard_hours', 8, 4); // How long it takes to do this once (in hours)
            $table->integer('annual_volume')->nullable(); // How many times this happens per year (for health service activities)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workload_activities');
    }
};

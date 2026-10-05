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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Intensive Care Unit (ICU)"
            $table->string('type')->nullable(); // e.g., "Inpatient", "Emergency"
            $table->integer('current_staff'); // The actual number of nurses currently working here
            $table->integer('available_working_time_hours'); // AWT calculated for this specific department's cadre
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

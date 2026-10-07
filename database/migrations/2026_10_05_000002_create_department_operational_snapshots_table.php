<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_operational_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operational_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('patient_count')->default(0);
            $table->unsignedInteger('high_acuity_patient_count')->default(0);
            $table->unsignedSmallInteger('on_duty_staff');
            $table->unsignedSmallInteger('required_on_duty_staff');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['operational_round_id', 'department_id'], 'ops_snapshot_round_department_unique');
            $table->index(['department_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_operational_snapshots');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobilization_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operational_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('to_department_id')->constrained('departments')->restrictOnDelete();
            $table->unsignedSmallInteger('staff_count');
            $table->string('status', 24)->default('pending');
            $table->text('rationale');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamps();

            $table->index(['operational_round_id', 'status']);
            $table->index(['from_department_id', 'status']);
            $table->index(['to_department_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobilization_recommendations');
    }
};

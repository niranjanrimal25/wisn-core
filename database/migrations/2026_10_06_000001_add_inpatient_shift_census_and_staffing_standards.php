<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->string('operational_unit_type', 64)->default('general_ward');
        });

        // Classify existing departments for the inpatient-only operations workflow.
        DB::table('departments')
            ->whereRaw('LOWER(type) LIKE ?', ['%outpatient%'])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%outpatient%'])
            ->orWhereRaw('LOWER(name) LIKE ?', ['%opd%'])
            ->update(['operational_unit_type' => 'outpatient']);

        DB::table('departments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%picu%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%pediatric intensive care%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%paediatric intensive care%']);
            })
            ->update(['operational_unit_type' => 'picu']);

        DB::table('departments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%nicu%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%neonatal intensive care%']);
            })
            ->update(['operational_unit_type' => 'nicu']);

        DB::table('departments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%icu%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%intensive care%']);
            })
            ->whereNotIn('operational_unit_type', ['outpatient', 'picu', 'nicu'])
            ->update(['operational_unit_type' => 'icu']);

        DB::table('departments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%emergency%'])
                    ->orWhereRaw('LOWER(type) LIKE ?', ['%emergency%']);
            })
            ->where('operational_unit_type', '!=', 'outpatient')
            ->update(['operational_unit_type' => 'emergency_inpatient']);

        DB::table('departments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%surgical%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%surgery%']);
            })
            ->where('operational_unit_type', '!=', 'outpatient')
            ->update(['operational_unit_type' => 'surgical_ward']);

        DB::table('departments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%pediatric%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%paediatric%']);
            })
            ->whereNotIn('operational_unit_type', ['outpatient', 'icu', 'picu', 'nicu', 'emergency_inpatient'])
            ->update(['operational_unit_type' => 'pediatric_ward']);

        DB::table('departments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%maternity%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%obstetric%']);
            })
            ->where('operational_unit_type', '!=', 'outpatient')
            ->update(['operational_unit_type' => 'maternity_ward']);

        Schema::create('operational_staffing_standards', function (Blueprint $table) {
            $table->id();
            $table->string('unit_type', 64);
            $table->string('shift_code', 16);
            $table->decimal('patients_per_nurse', 6, 2);
            $table->unsignedSmallInteger('minimum_nurses_per_shift')->default(0);
            $table->string('source_name');
            $table->string('source_version', 100)->nullable();
            $table->text('source_url')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['unit_type', 'shift_code', 'is_active', 'effective_from'], 'ops_standard_lookup_index');
        });

        Schema::table('operational_rounds', function (Blueprint $table) {
            $table->date('census_date')->nullable();
            $table->string('shift_code', 16)->nullable();
            $table->timestamp('observed_at')->nullable();
            $table->index(['census_date', 'shift_code', 'captured_at'], 'ops_round_date_shift_index');
        });

        Schema::table('department_operational_snapshots', function (Blueprint $table) {
            $table->unsignedSmallInteger('on_duty_staff')->nullable()->change();
            // Kept nullable for compatibility with legacy manually-entered targets;
            // new snapshots use calculated_required_staff from an approved standard.
            $table->unsignedSmallInteger('required_on_duty_staff')->nullable()->change();
        });

        Schema::table('department_operational_snapshots', function (Blueprint $table) {
            $table->foreignId('staffing_standard_id')
                ->nullable()
                ->constrained('operational_staffing_standards')
                ->nullOnDelete();
            $table->unsignedInteger('calculated_required_staff')->nullable();
        });
    }

    public function down(): void
    {
        DB::table('department_operational_snapshots')
            ->whereNull('on_duty_staff')
            ->update(['on_duty_staff' => 0]);
        DB::table('department_operational_snapshots')
            ->whereNull('required_on_duty_staff')
            ->update(['required_on_duty_staff' => 0]);

        Schema::table('department_operational_snapshots', function (Blueprint $table) {
            $table->dropForeign(['staffing_standard_id']);
            $table->dropColumn(['staffing_standard_id', 'calculated_required_staff']);
        });

        Schema::table('department_operational_snapshots', function (Blueprint $table) {
            $table->unsignedSmallInteger('on_duty_staff')->nullable(false)->change();
            $table->unsignedSmallInteger('required_on_duty_staff')->nullable(false)->change();
        });

        Schema::table('operational_rounds', function (Blueprint $table) {
            $table->dropIndex('ops_round_date_shift_index');
            $table->dropColumn(['census_date', 'shift_code', 'observed_at']);
        });

        Schema::dropIfExists('operational_staffing_standards');

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('operational_unit_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};

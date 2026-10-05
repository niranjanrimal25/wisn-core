<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'available_working_time_hours')) {
                $table->dropColumn('available_working_time_hours');
            }
            $table->unsignedSmallInteger('working_days_per_year')->default(260)->after('current_staff');
            $table->unsignedSmallInteger('public_holidays')->default(13)->after('working_days_per_year');
            $table->unsignedSmallInteger('annual_leave_days')->default(18)->after('public_holidays');
            $table->unsignedSmallInteger('sick_leave_days')->default(12)->after('annual_leave_days');
            $table->unsignedSmallInteger('training_days')->default(5)->after('sick_leave_days');
            $table->unsignedTinyInteger('working_hours_per_day')->default(8)->after('training_days');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn([
                'working_days_per_year', 'public_holidays', 'annual_leave_days',
                'sick_leave_days', 'training_days', 'working_hours_per_day',
            ]);
            $table->integer('available_working_time_hours')->default(2000);
        });
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Department extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function activities()
    {
        return $this->hasMany(WorkloadActivity::class);
    }

    protected function availableWorkingTimeHours(): Attribute
    {
        return Attribute::get(fn () =>
            ($this->working_days_per_year
                - $this->public_holidays
                - $this->annual_leave_days
                - $this->sick_leave_days
                - $this->training_days
            ) * $this->working_hours_per_day
        );
    }
}

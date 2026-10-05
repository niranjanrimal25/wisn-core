<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadActivity extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relationship back to the department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

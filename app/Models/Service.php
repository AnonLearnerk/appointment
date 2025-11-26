<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Service extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_service');
    }

    // Accessor for created_at with timezone and 12-hour format
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->timezone('Asia/Manila')
            ->format('M d, Y h:i A');
    }

    // Accessor for updated_at with timezone and 12-hour format
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)
            ->timezone('Asia/Manila')
            ->format('M d, Y h:i A');
    }
}

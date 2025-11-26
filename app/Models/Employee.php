<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    // use SoftDeletes;

    protected $guarded = [];


    protected $casts = [
        'days' => 'array',
        'social' => 'array',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'employee_service');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_service');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

}

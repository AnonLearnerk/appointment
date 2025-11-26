<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    // Appointment Model
    protected $fillable = [
        'user_id',
        'employee_id',
        'service_id',
        'category_id',
        'booking_id',
        'booking_date',
        'booking_time',
        'status',
        'name',
        'email',
        'phone',
        'group_type',
        'num_members',
        'description',
    ];



    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


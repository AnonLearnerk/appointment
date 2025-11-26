<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Spatie\Permission\Traits\HasRoles;
use App\Models\Employee;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Primary key type and auto-incrementing settings for UUIDs
     */
    protected $keyType = 'string';   // tells Laravel the primary key is a string
    public $incrementing = false;    // disable auto-increment

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',             // add id here since Firebase provides it
        'name',
        'email',
        'password',
        'phone',
        'status',
        'user_type',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // AdminLTE / frontend image helpers
    public function adminlte_profile_url()
    {
        return "/profile";
    }

    public function adminlte_image()
    {
        $userImage = \Auth::user()->image;

        if ($userImage) {
            if (strpos($userImage, 'https://') === 0) {
                return $userImage;
            } else {
                return asset('uploads/images/profile/' . $userImage);
            }
        } else {
            return asset('vendor/adminlte/dist/img/gravtar.jpg');
        }
    }

    public function profileImage()
    {
        $userImage = $this->image;

        if (!empty($userImage)) {
            return asset('uploads/images/profile/' . $userImage);
        } else {
            return asset('vendor/adminlte/dist/img/gravtar.jpg');
        }
    }

    // Frontend user image for booking
    public function employeeImage()
    {
        $userImage = $this->image;

        if (!empty($userImage)) {
            return asset('uploads/images/profile/' . $userImage);
        } else {
            return asset('vendor/adminlte/dist/img/gravtar.jpg');
        }
    }
}

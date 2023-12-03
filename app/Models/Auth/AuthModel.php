<?php

namespace App\Models\Auth;

use App\Models\Master\RatingModel;
use App\Models\Users\AdminModel;
use App\Models\Users\CustomerModel;
use App\Models\Users\DriverModel;
use App\Models\Users\MitraModel;
use App\Models\Users\MitraServicesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AuthModel extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'access_auth';
    protected $keyType = 'string'; // Set the primary key type to 'string'
    public $incrementing = false; // Disable auto-incrementing primary key
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id', // This is the primary key
        'email',
        'password',
        'role',
        'status',
        'device_id',
        'created_at',
    ];

    public $timestamps = false; // Disable the default timestamps for this model

    // Relationship with references table
    public function admin()
    {
        return $this->hasOne(AdminModel::class, 'user_id', 'user_id');
    }

    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'user_id', 'user_id');
    }

    public function mitra()
    {
        return $this->hasOne(MitraModel::class, 'user_id', 'user_id');
    }

    public function driver()
    {
        return $this->hasOne(DriverModel::class, 'user_id', 'user_id');
    }

    public function ratingUser()
    {
        return $this->hasMany(RatingModel::class, 'mitra_id', 'user_id');
    }

    public function ratingMitra()
    {
        return $this->hasMany(RatingModel::class, 'mitra_id', 'user_id');
    }


    public function scopeWithRole($query, $role = 1)
    {
        return $query->where('role', $role);
    }

    // Method to get user by email
    public function getByEmailAdmin($email)
    {
        return $this->where('email', $email)
            ->with('admin') // Eager load the user relationship
            ->first();
    }

    // Method to get user by email
    public function getByEmailDriver($email)
    {
        return $this->where('email', $email)
            ->with('driver') // Eager load the user relationship
            ->first();
    }

    // Method to get user by email
    public function getByEmailCustomer($email)
    {
        return $this->where('email', $email)
            ->with('customer') // Eager load the user relationship
            ->first();
    }

    // Method to get user by email
    public function getByEmailMitra($email)
    {
        return $this->where('email', $email)
            ->with('mitra') // Eager load the mitra relationship
            ->first();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}

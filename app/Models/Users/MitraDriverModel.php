<?php

namespace App\Models\Users;

use App\Models\Auth\AuthModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraDriverModel extends Model
{
    use HasFactory;

    protected $table = 't_mitra_drivers'; // Set the table name if different from the default

    public $timestamps = false; // Disable timestamps

    protected $fillable = [
        'id',
        'mitra_id', // The user_id associated with the service
        'driver_id', // The service_id associated with the user
        // Add other fillable fields as needed
    ];

    public function auth()
    {
        return $this->hasOne(AuthModel::class, 'user_id', 'driver_id');
    }

    public function user()
    {
        return $this->hasOne(DriverModel::class, 'user_id', 'driver_id');
    }
}

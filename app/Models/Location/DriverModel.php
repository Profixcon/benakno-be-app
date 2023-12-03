<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverModel extends Model
{
    use HasFactory;

    protected $table = 'access_driver';
    protected $keyType = 'string'; // Set the primary key type to 'string'
    public $incrementing = false; // Disable auto-incrementing primary key
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'name',
        'photo',
        'phone',
        'latitude',
        'longitude',
    ];
    public $timestamps = false; // Disable the default timestamps for this model
}

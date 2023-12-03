<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminModel extends Model
{
    use HasFactory;

    protected $table = 'access_user';
    protected $keyType = 'string'; // Set the primary key type to 'string'
    public $incrementing = false; // Disable auto-incrementing primary key
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'name',
        'photo',
        'birthdate',
        'phone',
        'address',
    ];
    public $timestamps = false; // Disable the default timestamps for this model
}

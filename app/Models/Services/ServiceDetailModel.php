<?php

namespace App\Models\Services;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDetailModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'service_id',
        'name',
        'cost',
    ];

    protected $primaryKey = 'id'; // the primary key

    public $incrementing = false; // Disable auto-increment

    protected $keyType = 'string'; // Set the key type to string

    public $timestamps = false; // Disable timestamps

    protected $table = 't_services_detail'; // Specify the table name
}

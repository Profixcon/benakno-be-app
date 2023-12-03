<?php

namespace App\Models\Users;

use App\Models\Master\ServicesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraServicesModel extends Model
{
    use HasFactory;

    protected $table = 't_mitra_services'; // Set the table name if different from the default

    protected $keyType = 'string'; // Set the primary key type to 'string'

    public $timestamps = false; // Disable timestamps

    protected $fillable = [
        'id',
        'user_id', // The user_id associated with the service
        'services_id', // The service_id associated with the user
        'price',
        'status'
        // Add other fillable fields as needed
    ];

    public function service()
    {
        return $this->hasOne(ServicesModel::class, 'id', 'services_id');
    }
}

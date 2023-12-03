<?php

namespace App\Models\Master;

use App\Models\Users\CustomerModel;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleModel extends Model
{
    use HasFactory, RecordSignature, softDeletes;

    protected $fillable = [
        'user_id',
        'brand_id',
        'type_id',
        'year',
        'type_vehicle',
        'nopol'
    ];

    protected $primaryKey = 'id'; // the primary key

    public $incrementing = false; // Disable auto-increment

    protected $keyType = 'string'; // Set the key type to string

    protected $table = 't_vehicle'; // Specify the table name

    public function user()
    {
        return $this->hasOne(CustomerModel::class, 'user_id', 'user_id');
    }

    public function brand()
    {
        return $this->hasOne(VehicleBrandModel::class, 'id', 'brand_id');
    }

    public function type()
    {
        return $this->hasOne(VehicleTypeModel::class, 'id', 'type_id');
    }
}

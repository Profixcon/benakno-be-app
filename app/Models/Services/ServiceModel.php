<?php

namespace App\Models\Services;

use App\Models\Chats\ChatRoomModel;
use App\Models\Master\RatingModel;
use App\Models\Master\VehicleModel;
use App\Models\Users\CustomerModel;
use App\Models\Users\MitraModel;
use App\Models\Users\MitraServicesModel;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceModel extends Model
{
    use HasFactory, RecordSignature, softDeletes;

    protected $fillable = [
        'service_id',
        'room_chat_id',
        'user_id',
        'vehicle_id',
        'mitra_id',
        'description',
        'status',
        'total',
        'service_fee',
        'payment_method',
        'sub_total',
        'customer_lat',
        'customer_long',
    ];

    protected $primaryKey = 'id'; // the primary key

    public $incrementing = false; // Disable auto-increment

    protected $keyType = 'string'; // Set the key type to string

    protected $table = 't_services'; // Specify the table name

    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'user_id', 'user_id');
    }

    public function mitra()
    {
        return $this->hasOne(MitraModel::class, 'user_id', 'mitra_id');
    }

    public function vehicle()
    {
        return $this->hasOne(VehicleModel::class, 'id', 'vehicle_id');
    }

    public function service()
    {
        return $this->hasOne(MitraServicesModel::class, 'id', 'service_id');
    }

    public function bill()
    {
        return $this->hasMany(ServiceDetailModel::class, 'service_id', 'id');
    }

    public function rating()
    {
        return $this->hasMany(RatingModel::class, 'service_id', 'id');
    }

    public function chat_room()
    {
        return $this->hasOne(ChatRoomModel::class, 'service_id', 'id');
    }
}

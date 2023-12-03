<?php

namespace App\Models\Master;

use App\Models\Services\ServiceModel;
use App\Models\Users\CustomerModel;
use App\Models\Users\MitraModel;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingModel extends Model
{
    use HasFactory, RecordSignature, softDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'mitra_id',
        'rating',
        'description'
    ];

    protected $primaryKey = 'id'; // the primary key

    public $incrementing = false; // Disable auto-increment

    protected $keyType = 'string'; // Set the key type to string

    protected $table = 't_rating'; // Specify the table name

    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'user_id', 'user_id');
    }

    public function mitra()
    {
        return $this->hasOne(MitraModel::class, 'user_id', 'mitra_id');
    }

    public function service()
    {
        return $this->hasOne(ServiceModel::class, 'id', 'service_id');
    }
}

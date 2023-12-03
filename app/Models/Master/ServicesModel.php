<?php

namespace App\Models\Master;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicesModel extends Model
{
    use HasFactory, RecordSignature, softDeletes;

    protected $fillable = [
        'name',
        'photo',
        'desc',
        'status'
    ];

    protected $primaryKey = 'id'; // the primary key

    public $incrementing = false; // Disable auto-increment

    protected $keyType = 'string'; // Set the key type to string

    protected $table = 'm_services'; // Specify the table name
}

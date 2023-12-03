<?php

namespace App\Models\Chats;

use App\Models\Services\ServiceModel;
use App\Models\Users\CustomerModel;
use App\Models\Users\MitraModel;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoomModel extends Model
{
    use HasFactory, RecordSignature, softDeletes;

    protected $fillable = [
        'service_id',
        'sender',
        'participants',
        'status',
    ];

    protected $primaryKey = 'id'; // the primary key

    public $incrementing = false; // Disable auto-increment

    protected $keyType = 'string'; // Set the key type to string

    protected $table = 't_chat_room'; // Specify the table name

    public function service()
    {
        return $this->hasOne(ServiceModel::class, 'id', 'service_id');
    }

    public function chats()
    {
        return $this->hasMany(ChatModel::class, 'id', 'room_id');
    }
}

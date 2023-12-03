<?php

namespace App\Models\Chats;

use App\Models\Users\CustomerModel;
use App\Models\Users\MitraModel;
use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatModel extends Model
{
    use HasFactory, RecordSignature, softDeletes;

    protected $fillable = [
        'room_id',
        'sender_id',
        'message',
        'file',
    ];

    protected $primaryKey = 'id'; // the primary key

    public $incrementing = false; // Disable auto-increment

    protected $keyType = 'string'; // Set the key type to string

    protected $table = 't_chats'; // Specify the table name
}

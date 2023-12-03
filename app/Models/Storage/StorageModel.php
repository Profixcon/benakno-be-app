<?php

namespace App\Models\Storage;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageModel extends Model
{
    use HasFactory, RecordSignature;

    protected $table = 'storage';

    protected $fillable = [
        'id',
        'filename',
        'path',
        'type',
        'expires_at',
        'folder',
        'created_at',
        'createad_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    // Define any additional relationships or methods here if needed
}

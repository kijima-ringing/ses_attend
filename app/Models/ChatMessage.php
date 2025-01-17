<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'user_id',
        'chat_room_id',
        'message',
        'read_flag',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'integer';
}
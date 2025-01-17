<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }
}
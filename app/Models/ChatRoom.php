<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $table = 'chat_rooms';

    protected $fillable = [
        'id',
        'admin_id',
        'user_id',
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

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
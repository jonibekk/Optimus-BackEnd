<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends BaseModel
{
    use HasFactory;

    protected $table = 'chat_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_room_id',
        'user_id',
        'message',
    ];

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

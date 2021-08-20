<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatRoomMember extends BaseModel
{
    use HasFactory;

    protected $table = 'chat_room_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'chat_room_id',
        'member_type',
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

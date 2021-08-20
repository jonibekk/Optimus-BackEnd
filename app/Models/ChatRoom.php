<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatRoom extends BaseModel
{
    use HasFactory;

    protected $table = 'chat_rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'name',
        'description',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'chat_room_members');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageAttachment extends BaseModel
{
    use HasFactory;

    protected $table = 'message_attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_message_id',
        'attached_file_id',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id', 'id');
    }

    public function attachedFile()
    {
        return $this->belongsTo(AttachedFiles::class, 'attached_file_id', 'id');
    }
}

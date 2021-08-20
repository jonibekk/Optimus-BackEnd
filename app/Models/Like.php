<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Like extends BaseModel
{
    use HasFactory;

    protected $table = 'likes';

    public function action()
    {
        return $this->morphedByMany(ActionPosts::class, 'likable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

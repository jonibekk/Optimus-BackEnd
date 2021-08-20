<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comments extends BaseModel
{
    use HasFactory;
    
    protected $table = 'comments';

    public function action()
    {
        return $this->morphedByMany(ActionPosts::class, 'commentable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}

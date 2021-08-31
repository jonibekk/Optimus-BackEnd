<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comments extends BaseModel
{
    use HasFactory;
    
    protected $table = 'comments';

    public function action()
    {
        return $this->belongsTo(ActionPosts::class, 'action_post_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}

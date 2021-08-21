<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionPosts extends BaseModel
{
    use HasFactory;

    protected $table = 'action_posts';
    
    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    // protected $dateFormat = 'U';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'goal_id',
        'kr_id',
        'body',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function goal()
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'id');
    }

    public function keyResult()
    {
        return $this->belongsTo(KeyResults::class, 'kr_id', 'id');
    }

    public function attachedFile()
    {
        return $this->belongsToMany(AttachedFiles::class, 'action_files', 'action_id', 'id');
    }
    
    public function comment()
    {
        return $this->hasMany(Comments::class);
    }

    public function like()
    {
        return $this->morphMany(Like::class, 'likeable', 'likeable_type');
    }

}

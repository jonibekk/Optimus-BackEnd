<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goal extends BaseModel
{
    use HasFactory;

    protected $table = 'goals';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'progress',
        'name',
        'description',
        'color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function member()
    {
        return $this->belongsToMany(User::class, 'goal_members');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'goal_members');
    }

    public function kr()
    {
        return $this->hasMany(KeyResults::class);
    }

    public function action()
    {
        return $this->hasMany(ActionPosts::class);
    }
}

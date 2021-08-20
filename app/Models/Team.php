<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends BaseModel
{
    use HasFactory;

    protected $table = 'teams';

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'timezone',
        'service_use_type',
        'service_status',
        'logo_url',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'team_members');
    }
    
    public function goal()
    {
        return $this->belongsToMany(Goal::class, 'goal_members');
    }
    
    public function chatRoom()
    {
        return $this->hasMany(ChatRoom::class);
    }
    
}

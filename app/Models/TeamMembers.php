<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMembers extends BaseModel
{
    use HasFactory;

    protected $table = 'team_members';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'member_type',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function team() 
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}

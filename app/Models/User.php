<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'bio',
        'timezone',
        'avatar_url',
        'email_verified',
        'current_team_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function team()
    {
        return $this->belongsToMany(Team::class, 'team_members');
    }

    public function goal()
    {
        return $this->belongsToMany(Goal::class, 'goal_members');
    }

    public function keyResult()
    {
        return $this->hasMany(KeyResults::class);
    }

    public function action()
    {
        return $this->hasMany(ActionPosts::class);
    }

    public function chatRoom()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_members');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return []; 
    }

}

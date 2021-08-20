<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invitations extends BaseModel
{
    use HasFactory;

    protected $table = 'invitations';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'from_user_id',
        'email',
        'email_token',
        'expires_at',
    ];
}

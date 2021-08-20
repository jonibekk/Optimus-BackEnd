<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeyResults extends BaseModel
{
    use HasFactory;

    protected $table = 'key_results';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'goal_id',
        'unit',
        'currency_type',
        'target_value',
        'progress',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'id');
    }

    public function action()
    {
        return $this->hasMany(ActionPosts::class, 'kr_id', 'id');
    }
}

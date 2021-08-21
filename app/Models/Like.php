<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Like extends BaseModel
{
    use HasFactory;

    protected $table = 'likes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'likeable_id',
        'likeable_type',
    ];

    public function likeable()
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttachedFiles extends BaseModel
{
    use HasFactory;

    protected $table = 'attached_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'team_id',
        'filename',
        'file_ext',
        'file_url',
        'file_size',
        'file_type',
    ];

    public function action()
    {
        return $this->belongsToMany(ActionPosts::class, 'action_files');
    }
}

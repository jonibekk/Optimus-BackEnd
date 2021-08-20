<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionFiles extends BaseModel
{
    use HasFactory;

    protected $table = 'action_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'action_id',
        'attached_file_id',
    ];

    public function action()
    {
        return $this->belongsTo(ActionPosts::class, 'action_id', 'id');
    }

    public function attachedFile()
    {
        return $this->belongsTo(AttachedFiles::class, 'attached_file_id', 'id');
    }
}

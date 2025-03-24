<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    // Fields that are mass assignable
    protected $fillable = [
        'path',
        'thumb_path',
        'type'
    ];

    /**
     * Get the comment that this file is attached to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}

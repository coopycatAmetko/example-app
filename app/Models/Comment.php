<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Comment extends Model
{
    use NodeTrait;
    
    // Fields that are mass assignable
    protected $fillable = [
        'post_id',
        'user_name',
        'email',
        'homepage',
        'text',
        '_lft',
        '_rgt',
        'parent_id',
    ];

    /**
     * Get post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get files attached to the comment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }
}
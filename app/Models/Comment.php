<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = 
    [
        'lang',
        'body',
        'user_id',
        'post_id'
    ];

    /**
     * Get the user for the comments.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Get the post for the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class,'post_id');
    }

    /**
     * Get the reactos for the comment.
     */
    public function reactos(): MorphToMany
    {
        return $this->morphToMany(React::class,'reactoble')->withPivot('user_id')->withTimestamps();
    }

    protected static function booted()
    {
        # delete reactions
        static::deleted(function ($comment) {
            DB::table('reactobles')
            ->where('reactoble_type', Comment::class)
            ->where('reactoble_id', $comment->id)
            ->delete();
        });
    }
}

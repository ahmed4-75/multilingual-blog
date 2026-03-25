<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = 
    [
        'lang',
        'body',
        'user_id'
    ];

    /**
     * Get the user for the posts.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class,'post_id');
    }

    /**
     * Get the reactos for the post.
     */
    public function reactos(): MorphToMany
    {
        return $this->morphToMany(React::class,'reactoble')->withPivot('user_id')->withTimestamps();
    }


    protected static function booted()
    {
        # delete reactions
        static::forceDeleted(function ($post) {
            DB::table('reactobles')
            ->where('reactoble_type', Post::class)
            ->where('reactoble_id', $post->id)
            ->delete();
    
            DB::table('reactobles')
            ->where('reactoble_type', Comment::class)
            ->whereIn('reactoble_id', function ($query) use ($post) {
                $query->select('id')->from('comments')->where('post_id', $post->id);
            })
            ->delete();

            // 3️⃣ حذف كل التعليقات المرتبطة بالبوست نهائيًا
            $post->comments()->delete();
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class React extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'reactos';

    protected $fillable = ['name'];

    /**
     * Get the posts that are assigned this tag.
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'reactoble');
    }
 
    /**
     * Get the comments that are assigned this tag.
     */
    public function comments(): MorphToMany
    {
        return $this->morphedByMany(Comment::class, 'reactoble');
    }
}

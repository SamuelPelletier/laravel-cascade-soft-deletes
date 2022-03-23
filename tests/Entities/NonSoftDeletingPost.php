<?php

namespace Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Pellesam\Database\Support\CascadeSoftDeleteAndRestore;

class NonSoftDeletingPost extends Model
{
    use CascadeSoftDeleteAndRestore;

    protected $table = 'posts';

    protected $cascadeDeletes = ['comments'];

    protected $fillable = ['title', 'body'];

    public function comments()
    {
        return $this->hasMany('Tests\Entities\Comment', 'post_id');
    }
}

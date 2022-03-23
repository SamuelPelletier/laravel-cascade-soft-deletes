<?php

namespace Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Pellesam\Database\Support\CascadeSoftDeleteAndRestore;

class Post extends Model
{
    use SoftDeletes, CascadeSoftDeleteAndRestore;

    public $dates = ['deleted_at'];

    protected $cascadeDeletes = ['comments', 'postType'];

    protected $fillable = ['title', 'body'];

    public function comments()
    {
        return $this->hasMany('Tests\Entities\Comment');
    }

    public function postType()
    {
        return $this->hasOne('Tests\Entities\PostType', 'post_id');
    }
}

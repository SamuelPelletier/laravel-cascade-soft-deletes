<?php

namespace Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Pellesam\Database\Support\CascadeSoftDeleteAndRestore;

class Author extends Model
{
    use SoftDeletes, CascadeSoftDeleteAndRestore;

    public $dates = ['deleted_at'];

    protected $cascadeRelations = ['posts', 'posttypes'];

    protected $fillable = ['name'];

    public function posts()
    {
        return $this->hasMany('Tests\Entities\Post');
    }

    public function posttypes()
    {
        return $this->belongsToMany('Tests\Entities\PostType', 'authors__post_types', 'author_id', 'posttype_id');
    }
}

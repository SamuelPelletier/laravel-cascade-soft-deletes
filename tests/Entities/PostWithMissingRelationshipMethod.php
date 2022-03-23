<?php

namespace Tests\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Pellesam\Database\Support\CascadeSoftDeleteAndRestore;

class PostWithMissingRelationshipMethod extends Model
{
    use SoftDeletes, CascadeSoftDeleteAndRestore;

    public $dates = ['deleted_at'];

    protected $table = 'posts';

    protected $cascadeDeletes = 'comments';

    protected $fillable = ['title', 'body'];
}

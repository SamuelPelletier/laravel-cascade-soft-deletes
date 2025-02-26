# Cascading soft delete and restore for the Laravel PHP Framework

## Introduction

In scenarios when you delete a parent record - say for example a blog post - you may want to also delete any comments
associated with it as a form of self-maintenance of your data.

Normally, you would use your database's foreign key constraints, adding an `ON DELETE CASCADE` rule to the foreign key
constraint in your comments table.

It may be useful to be able to restore a parent record after it was deleted. In those instances, you may reach for
Laravel's [soft deleting](https://laravel.com/docs/5.2/eloquent#soft-deleting) functionality.

In doing so, however, you lose the ability to use the cascading delete functionality that your database would otherwise
provide. That is where this package aims to bridge the gap in functionality when using the `SoftDeletes` trait.

## Code Samples

```php
<?php

namespace App;

use App\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes, CascadeSoftDeleteAndRestore;

    protected $cascadeRelations = ['comments'];

    protected $dates = ['deleted_at'];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

Now you can delete an `App\Post` record, and any associated `App\Comment` records will be deleted. If the `App\Comment`
record implements the `CascadeSoftDeleteAndRestore` trait as well, it's children will also be deleted and so on.

```php
$post = App\Post::find($postId)
$post->delete(); // Soft delete the post, which will also trigger the delete() method on any comments and their children.
```

**Note**: It's important to know that when you cascade your soft deleted child records, there is no way to know which
were deleted by the cascading operation, and which were deleted prior to that. This means that when you restore the blog
post, the associated comments will not be.

Because this trait hooks into the `deleting` Eloquent model event, we can prevent the parent record from being deleted
as well as any child records, if any exception is triggered. A `LogicException` will be triggered if the model does not
use the `Illuminate\Database\Eloquent\SoftDeletes` trait, or if any of the defined `cascadeDeletes` relationships do not
exist, or do not return an instance of `Illuminate\Database\Eloquent\Relations\Relation`.

**Additional Note**:  If you already have existing event listeners in place for a model that is going to cascade soft
deletes, you can adjust the priority or firing order of events to have CascadeSoftDeleteAndRestore fire after your
event. To do this you can set the priority of your deleting event listener to be 1.

`MODEL::observe( MODELObserver::class, 1 );`  The second param is the priority.

`MODEL::deleting( MODELObserver::class, 1 );`

As of right now this is not documented in the Larvel docs, but just know that the default priority is `0` for all
listeners, and that `0` is the lowest priority. Passing a param of greater than `0` to your listener will cause your
listener to fire before listeners with default priority of `0`

## Installation

This trait is installed via [Composer](http://getcomposer.org/). To install, simply add to your `composer.json` file:

```
$ composer require pellesam/laravel-cascade-soft-delete-and-restore
```

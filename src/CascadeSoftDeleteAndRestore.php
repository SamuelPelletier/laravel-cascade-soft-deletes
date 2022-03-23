<?php

namespace Pellesam\Database\Support;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait CascadeSoftDeleteAndRestore
{
    /**
     * Boot the trait.
     *
     * Listen for the deleting event of a soft deleting model, and run
     * the delete operation for any configured relationship methods.
     *
     * Listen for the restoring event of a soft deleting model, and run
     * the restore operation for any configured relationship methods.
     *
     * @throws \LogicException
     */
    protected static function bootCascadeSoftDeleteAndRestore(): void
    {
        static::deleting(function ($model) {
            $model->validateSoftDelete();

            $model->runCascadingDeletes();
        });

        static::restoring(function ($model) {
            // Check if the model has soft delete because we can't restore model without soft delete
            $model->validateSoftDelete();

            $model->runCascadingRestore();
        });
    }

    /**
     * Validate that the calling model is correctly setup for cascading soft deletes.
     *
     * @throws \Exception
     */
    protected function validateSoftDelete(): void
    {
        if (!$this->implementsSoftDeletes()) {
            throw new static(sprintf('%s does not implement Illuminate\Database\Eloquent\SoftDeletes',
                get_called_class()));
        }

        if ($invalidCascadingRelationships = $this->hasInvalidCascadingRelationships()) {
            throw new static(sprintf(
                '%s [%s] must exist and return an object of type Illuminate\Database\Eloquent\Relations\Relation',
                Str::plural('Relationship', count($invalidCascadingRelationships)),
                join(', ', $invalidCascadingRelationships)
            ));
        }
    }

    /**
     * Determine if the current model implements soft deletes.
     *
     * @return bool
     */
    protected function implementsSoftDeletes(): bool
    {
        return method_exists($this, 'runSoftDelete');
    }

    /**
     * Determine if the current model has any invalid cascading relationships defined.
     *
     * A relationship is considered invalid when the method does not exist, or the relationship
     * method does not return an instance of Illuminate\Database\Eloquent\Relations\Relation.
     *
     * @return array
     */
    protected function hasInvalidCascadingRelationships(): array
    {
        return array_filter($this->getCascadingRelations(), function ($relationship) {
            return !method_exists($this, $relationship) || !$this->{$relationship}() instanceof Relation;
        });
    }

    /**
     * Fetch the defined cascading soft deletes for this model.
     *
     * @return array
     */
    protected function getCascadingRelations(): array
    {
        return isset($this->cascadeRelations) ? (array)$this->cascadeRelations : [];
    }

    /**
     * Run the cascading soft delete for this model.
     * Cascade delete the given relationship on the given mode.
     *
     * @return void
     */
    protected function runCascadingDeletes(): void
    {
        foreach ($this->getCascadingRelations() as $relationship) {
            foreach ($this->{$relationship}()->get() as $model) {
                $model->pivot ? $model->pivot->delete() : $model->delete();
            }
        }
    }

    /**
     * Run the cascading restore for this model.
     * Cascade restore the given relationship on the given mode.
     *
     * @return void
     */
    protected function runCascadingRestore(): void
    {
        $deletedDate = $this->deleted_at;
        foreach ($this->getCascadingRelations() as $relationship) {
            // Check if the relations will be soft deletable
            if (method_exists($this->{$relationship}()->getRelated(), 'runSoftDelete')) {
                foreach ($this->{$relationship}()->onlyTrashed()->get() as $model) {
                    if ($model->deleted_at->format('Y-m-d H:i') === $deletedDate->format('Y-m-d H:i')) {
                        $model->restore();
                    }
                }
            }
        }
    }
}

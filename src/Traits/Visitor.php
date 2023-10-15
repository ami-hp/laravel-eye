<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Models\Visit;

trait Visitor
{
    /**
     * Get all of the post's comments.
     * @return mixed
     */
    public function visits()
    {
        return $this->morphMany(Visit::class, 'visitor');
    }

    /**
     * Create a visit log.
     * @param Model|null $visitable
     * @return mixed
     */
    public function visit(?Model $visitable = NULL)
    {
        return app('ami-visitor-cacher')->setVisitor($this)->record($visitable);
    }


}

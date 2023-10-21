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

}

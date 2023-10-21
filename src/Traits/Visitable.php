<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Models\Visit;
use Ami\Eye\Observers\VisitableObserver;

trait Visitable
{    /**
 * Viewable boot logic.
 *
 * @return void
 */
    public static function bootVisitable()
    {
        static::observe(VisitableObserver::class);
    }
    /**
     * Get all of the model visit logs.
     *
     * @return mixed
     */
    public function visitLogs()
    {
        return $this->morphMany(Visit::class, 'visitable');
    }

}

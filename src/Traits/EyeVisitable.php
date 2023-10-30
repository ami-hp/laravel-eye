<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Models\Visit;
use Ami\Eye\Observers\VisitableObserver;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait EyeVisitable
{
    /**
     * Visitable boot logic.
     *
     * @return void
     */
    public static function bootEyeVisitable()
    {
        static::observe(VisitableObserver::class);
    }
    /**
     * Get all of the model visit logs.
     *
     * @return mixed
     */
    public function visits()
    {
        return $this->morphMany(Visit::class, 'visitable');
    }

    /**
     * @param string|null $unique
     * @param string|null $collection
     * @param Model|null  $visitor
     * @return Collection
     * @throws Exception
     */
    public function cachedVisits(?string $unique = null , ?string $collection = null , ?Model $visitor = null): Collection
    {
        $eye = eye($this)->viaCache();

        if($unique)
            $eye = $eye->unique($unique);

        if($collection)
            $eye = $eye->collection($collection);

        if($visitor)
            $eye = $eye->collection($visitor);

        return $eye->get();
    }
}

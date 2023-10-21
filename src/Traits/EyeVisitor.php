<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Models\Visit;
use Ami\Eye\Observers\VisitorObserver;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait EyeVisitor
{
    /**
     * Visitable boot logic.
     *
     * @return void
     */
    public static function bootVisitable()
    {
        static::observe(VisitorObserver::class);
    }
    /**
     * Get all of the post's comments.
     * @return mixed
     */
    public function visits()
    {
        return $this->morphMany(Visit::class, 'visitor');
    }

    /**
     * @param string|null     $unique
     * @param string|null     $collection
     * @param bool|Model|null $visitable
     * @return Collection
     * @throws Exception
     */
    public function cachedVisits(?string $unique = null , ?string $collection = null , $visitable = false): Collection
    {
        $eye = eye()->viaCache()->visitor($this)->visitable($visitable);

        if($unique) $eye = $eye->unique($unique);

        if($collection) $eye = $eye->collection($collection);

        return $eye->get();
    }
}

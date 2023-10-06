<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Models\Visit;

trait CanVisit
{
    /**
     * Get all of the post's comments.
     * @return mixed
     */
    public function visitLogs()
    {
        return $this->morphMany(Visit::class, 'visitor');
    }

    /**
     * Retrieve online users
     * @param $query
     * @param int $seconds
     * @return mixed
     */
    public function scopeOnline($query, $seconds = 180)
    {
        $time = now()->subSeconds($seconds);

        return $query->whereHas('visitLogs', function ($query) use ($time) {
            $query->where(config('visitor.table_name') . ".created_at", '>=', $time->toDateTime());
        });
    }

    /**
     * check if user is online
     * @param int $seconds
     * @return bool
     */
    public function isOnline($seconds = 180)
    {
        $time = now()->subSeconds($seconds);

        return $this->visitLogs()->whereHasMorph('user', [static::class], function ($query) use ($time) {
                $query
                    ->where('user_id', $this->id)
                    ->where(config('visitor.table_name') . ".created_at", '>=', $time->toDateTime());
            })->count() > 0;
    }
}

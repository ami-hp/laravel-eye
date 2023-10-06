<?php

namespace Ami\Eye\Models;

use Ami\Eye\Support\Period;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        if (!isset($this->table)) {
            $this->setTable(config('eye.table_name'));
        }
        parent::__construct($attributes);
    }
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'url',
        'method',
        'device',
        'headers',
        'request',
        'referer',
        'sessionId',
        'browser',
        'platform',
        'languages',
        'useragent',
        'visitor_id',
        'visitor_type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'request'   => 'array',
        'languages' => 'array',
        'headers'   => 'array',
    ];

    /**
     * Get the owning visitable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function visitable()
    {
        return $this->morphTo('visitable');
    }

    /**
     * Get the owning user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function visitor()
    {
        return $this->morphTo('visitor');
    }

    /**
     * Scope a query to only include views within the period.
     * @param Builder $query
     * @param Period  $period
     * @return Builder
     */
    public function scopeWithinPeriod(Builder $query, Period $period): Builder
    {
        $startDateTime = $period->getStartDateTime();
        $endDateTime   = $period->getEndDateTime();

        if ($startDateTime !== null && $endDateTime === null) {
            $query->where('viewed_at', '>=', $startDateTime);
        } elseif ($startDateTime === null && $endDateTime !== null) {
            $query->where('viewed_at', '<=', $endDateTime);
        } elseif ($startDateTime !== null && $endDateTime !== null) {
            $query->whereBetween('viewed_at', [$startDateTime, $endDateTime]);
        }

        return $query;
    }

    /**
     * Scope a query to only include views withing the collection.
     *
     * @param Builder      $query
     * @param  string|null $collection
     * @return Builder
     */
    public function scopeCollection(Builder $query, string $collection = null): Builder
    {
        return $query->where('collection', $collection);
    }
}


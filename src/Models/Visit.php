<?php

namespace Ami\Eye\Models;

use Ami\Eye\Support\Period;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method collection(string $collection)
 * @method whereVisitor(Model $visitor)
 * @method whereVisitable(Model $visitable)
 * @method whereUrl(string $url)
 * @method withInPeriod(Period $period)
 * @method static create(array $attributes)
 * @method static firstOrCreate(array $checkAttributes, array $insertAttributes)
 * @property int    visitable_id
 * @property int    visitor_id
 * @property string visitable_type
 * @property string visitor_type
 * @property string ip
 * @property string url
 * @property string method
 * @property string device
 * @property string browser
 * @property string referer
 * @property string platform
 * @property string unique_id
 * @property string useragent
 * @property string collection
 * @property string|array languages
 * @property string|array request
 * @property string|array headers
 * @property Carbon|string viewed_at
 */
class Visit extends Model
{

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(array $attributes = [])
    {
        if (!isset($this->table)) {
            $this->setTable(Container::getInstance()->make('config')['eye']['table_name']);
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
        'viewed_at' => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * Get the owning visitable model.
     *
     * @return MorphTo
     */
    public function visitable(): MorphTo
    {
        return $this->morphTo('visitable');
    }

    /**
     * Get the owning user model.
     *
     * @return MorphTo
     */
    public function visitor(): MorphTo
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
    public function scopeWhereCollection(Builder $query, string $collection = null): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * Scope a query to only include views withing the collection.
     *
     * @param Builder $query
     * @param Model   $visitable
     * @return Builder
     */
    public function scopeWhereVisitable(Builder $query, Model $visitable): Builder
    {
        return $query
            ->where('visitable_id'  , $visitable->id)
            ->where('visitable_type', get_class($visitable));
    }

    /**
     * Scope a query to only include views withing the collection.
     *
     * @param Builder $query
     * @param Model   $visitor
     * @return Builder
     */
    public function scopeWhereVisitor(Builder $query, Model $visitor): Builder
    {
        return $query
            ->where('visitor_id'  , $visitor->id)
            ->where('visitor_type', get_class($visitor));
    }
}


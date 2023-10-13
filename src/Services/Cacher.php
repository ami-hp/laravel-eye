<?php

namespace Ami\Eye\Services;

use Exception;
use Ami\Eye\Models\Visit;
use Ami\Eye\Support\Period;
use Ami\Eye\Jobs\ProcessVisits;
use Ami\Eye\Contracts\DataManagementInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Database\Eloquent\Model;

class Cacher implements DataManagementInterface
{

    public $cache_name;

    public $cached_visits;

    public $eye;

    public $period = null;

    /**
     * @var bool
     */
    private $once = false;

    public function __construct(EyeService $eye)
    {
        $this->eye = $eye;
        $this->cache_name    = $eye->config['eye']['cache']['key'] ?? "eye_records";
        $this->cached_visits = Cache::get($this->cache_name) ?? collect();
    }

    /**
     * @return bool
     */
    public function forget(): bool
    {
        $this->cached_visits = collect();

        return Cache::forget($this->cache_name);
    }

    public function period(Period $period): self
    {
        $this->period = $period;

        return $this;
    }


    public function once(): self
    {
        $this->once = true;

        return $this;
    }

    /**
     * @return Collection
     */
    public function get() : Collection
    {
        if(!$this->cached_visits) $this->cached_visits = collect();

        if($this->period !== null)
            return $this->cached_visits->period($this->period);

        return $this->cached_visits;
    }


    /**
     * Create a visit log.
     *
     * @param Model|null $visitable
     * @param Model|null $visitor
     * @param bool $uniqueView
     * @return Visit|bool
     * @throws Exception
     */
    public function record(?Model $visitable = null , ?Model $visitor = null, bool $once = false)
    {
        if ($visitor !== null) $this->eye()->setVisitor($visitor);

        if ($visitable !== null) $this->eye()->setVisitable($visitable);

        if(! $this->shouldRecord($once)) return false;

        $visit = $this->eye()->getCurrentVisit();

        if($this->maxedOut()) $this->pushCacheToDatabase();

        $this->pushVisitToCache($visit);

        return $visit;
    }

    /**
     * @return bool
     */
    public function pushCacheToDatabase() : bool
    {
        $visits = $this->cached_visits;

        //insert to database
        if($this->eye()->config['eye']['queue']){

            Queue::push(new ProcessVisits($visits , 1000));

        } else {

            $visits->chunk(1000)->each(function ($chunk) {
                $data = $chunk->map(function ($visit) {
                    $visit->request   = json_encode($visit->request);
                    $visit->languages = json_encode($visit->languages);
                    $visit->headers   = json_encode($visit->headers);
                    return $visit->toArray();
                })->toArray();

                Visit::query()->insert($data);
            });

        }


        //'queue:work'

        return $this->forget();
    }


    /**
     * @param Visit $visit
     * @return Visit
     */
    protected function pushVisitToCache(Visit $visit): Visit
    {

        $visits = $this->cached_visits;

        $visits = $visits->push($visit);

        Cache::set($this->cache_name , $visits);

        return $visit;

    }

    /**
     * @return bool
     */
    protected function maxedOut(): bool
    {
        if($this->eye()->config['eye']['cache']['max_count'] !== null)
            return $this->eye()->config['eye']['cache']['max_count'] <= $this->cached_visits->count();
        else
            return false;
    }

    /**
     * @throws Exception
     */
    protected function shouldRecord(bool $once = false): bool
    {
        // If ignore bots is true and the current visitor is a bot, return false
        if ($this->eye()->config['eye']['ignore_bots'] && $this->eye()->detector->isCrawler()) {
            return false;
        }

        if($once === true || $this->once){
            $visit = $this->eye()->getCurrentVisit();

            $cacheQuery = $this->cached_visits->whereVisitHappened($visit);

            if($cacheQuery->count() > 0) return false;
        }

        return true;
    }

    /**
     * @return EyeService
     */
    protected function eye(): EyeService
    {
        return $this->eye;
    }
}

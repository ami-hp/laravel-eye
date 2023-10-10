<?php

namespace Ami\Eye\Services;

use Ami\Eye\Jobs\ProcessVisits;
use Ami\Eye\Models\Visit;
use Ami\Eye\Support\Period;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Cacher
{

    public $cache_name;

    public $cached_visits;

    public $eye;

    public $period = null;

    public function __construct(EyeService $eye)
    {
        $this->eye = $eye;
        $this->cache_name    = $eye->config->get('cache.key') ?? "eye_records";
        $this->cached_visits = Cache::get($this->cache_name);
    }

    /**
     * @return bool
     */
    public function deleteAll(): bool
    {
        return Cache::forget($this->cache_name);
    }

    public function period(Period $period): Cacher
    {
        $this->period = $period;

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
     * @return Visit
     * @throws Exception
     */
    public function record(?Model $visitable = null , ?Model $visitor = null): Visit
    {
        if ($visitor !== null) $this->eye()->setVisitor($visitor);

        if ($visitable !== null) $this->eye()->setVisitable($visitable);

        $visit = $this->eye()->getCurrentVisit();

        if($this->maxedOut())
            $this->pushCacheToDatabase();


        $this->pushVisitToCache($visit);

//        if($this->storage === "database"){
//
//            if ($visitorModel !== null && method_exists($visitorModel, 'visitLogs')) {
//                $visit = $visitorModel->visitLogs()->create($data);
//            } else {
//                $visit = Visit::create($data);
//            }
//
//        }

        return $visit;
    }

    /**
     * @return void
     */
    public function pushCacheToDatabase() : void
    {
        $visits = $this->get();

        //insert to database
        dispatch(new ProcessVisits($visits , 1000));

        Cache::forget($this->cache_name);
    }

    /**
     * @return EyeService
     */
    protected function eye(): EyeService
    {
        return $this->eye;
    }

    /**
     * @param Visit $visit
     * @return Visit
     */
    protected function pushVisitToCache(Visit $visit): Visit
    {

        $visits = $this->get();

        $visits = $visits->push($visit);

        Cache::set($this->cache_name , $visits);

        return $visit;

    }

    /**
     * @return bool
     */
    protected function maxedOut(): bool
    {
        if($this->eye()->config->get('eye.cache.max_count') !== null)
            return $this->eye()->config->get('eye.cache.max_count') === $this->get()->count() ;
        else
            return false;
    }



}

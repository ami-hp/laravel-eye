<?php

namespace Ami\Eye\Services;

use Ami\Eye\Models\Visit;
use Ami\Eye\Support\Period;
use Ami\Eye\Traits\DataPreparation;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class Cacher
{


    public $cache_name;

    public $cached_visits;

    public $time;

    public function __construct()
    {
        $this->time          = Date::now();
        $this->cache_name    = $this->config['cache']['key'] ?? "eye_records";
        $this->cached_visits = Cache::get($this->cache_name);
    }

    /**
     * @return bool
     */
    public function deleteAllVisits(): bool
    {
        return Cache::forget($this->cache_name);
    }

    /**
     * @return Collection
     */
    public function get() : Collection
    {
        if(!$this->cached_visits) $this->cached_visits = collect();

        return $this->cached_visits;
    }



    /**
     * @return EyeService
     */
    protected function eye(): EyeService
    {
        return new EyeService;
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
     * Create a visit log.
     *
     * @param Model|null $user
     * @param Model|null $post
     * @return Visit
     * @throws Exception
     */
    public function record(?Model $visitable = null , ?Model $visitor = null): Visit
    {
        if ($visitor !== null) $this->eye()->setVisitor($visitor);

        if ($visitable !== null) $this->eye()->setVisitable($visitable);

        $visit = $this->eye()->getCurrentVisit();

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

}

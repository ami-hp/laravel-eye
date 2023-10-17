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

    protected $eye;

    protected $cached_visits;

    protected $period = null;

    protected $unique = null;

    protected $once = false;

    protected $collection;

    private $visitor = false;

    public function __construct(EyeService $eye)
    {
        $this->eye = $eye;
        $this->cache_name    = $eye->config['eye']['cache']['key'] ?? "eye_records";
        $this->cached_visits = Cache::get($this->cache_name) ?? collect();
    }


    /**
     * @param Period $period
     * @return $this
     */
    public function period(Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Which column should have unique value
     * @param string $column
     * @return $this
     */
    public function unique(string $column = 'unique_id'): self
    {
        $this->unique = $column;

        return $this;
    }

    /**
     * from collection column
     * @param string|null $name
     * @return $this
     */
    public function collection(?string $name = null): self
    {
        $this->collection = $name;
        $this->eye()->setCollection($name);

        return $this;
    }

    /**
     * @param Model|null $user
     * @param bool       $whereMode
     * @return self
     */
    public function visitor(?Model $user = null , bool $whereMode = true): self
    {
        $this->eye()->setVisitor($user);

        $this->visitor = $whereMode;

        return $this;
    }

    /**
     * @param Model|bool|null $post
     * @return self
     */
    public function visitable($post = null): self
    {
        if($post === false) //Disables Where
            $this->visitable = false;
        else // Enables Where Post for Model or Url for Null
            $this->eye()->setVisitable($post);

        return $this;
    }

    /**
     * @return Collection
     */
    public function get() : Collection
    {
        if(!$this->cached_visits) $this->cached_visits = collect();

        $get =  $this->cached_visits;

        return $this->query($get);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->get()->count();
    }

    /**
     * @return $this
     */
    public function once(): self
    {
        $this->once = true;

        return $this;
    }

    /**
     * Create a visit log.
     *
     * @param bool       $once
     * @param Model|null $visitable
     * @param Model|null $visitor
     * @return Visit|bool
     * @throws Exception
     */
    public function record(bool $once = false , ?Model $visitable = null , ?Model $visitor = null)
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
     * Delete Cache
     * @return bool
     */
    public function forget(): bool
    {
        $this->cached_visits = collect();

        return Cache::forget($this->cache_name);
    }

    /**
     * Insert all Visits in Cache to Database
     * @return bool
     */
    public function pushCacheToDatabase() : bool
    {
        $visits = $this->cached_visits;

        //insert to database
        if($this->eye()->config['eye']['queue'])
            Queue::push(new ProcessVisits($visits , 1000));
        else
            self::insert($visits , 1000);


        //'queue:work'

        return $this->forget();
    }

    /**
     * Protected Methods
     * -----------------
     */

    /**
     * Adds conditions to get
     * @param Collection $get
     * @return Collection
     */
    protected function query(Collection $get): Collection
    {
        if($this->visitor)
            $get = $get->whereVisitor($this->eye()->getVisitorModel());

        if($this->visitable){
            if($visitable = $this->eye()->getVisitableModel())
                $get = $get->whereVisitable($visitable);
            else
                $get = $get->whereUrl($this->eye()->url());
        }

        if($this->period !== null)
            $get = $get->period($this->period);

        if($this->unique !== null)
            $get = $get->unique($this->unique);

        if($this->collection !== null)
            $get = $get->whereCollection($this->collection);

        return $get;
    }

    /**
     * Gets Cache and insers a new Visit to it
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
     * Checks if the records reached to the maximum in config or not
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
     * Checks if the record should happen or not
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

    /**
     * Static Method
     * -------------
     */

    /**
     * @param     $visits
     * @param int $chunkNum
     * @return void
     */
    public static function insert($visits , int $chunkNum)
    {
        $visits->chunk($chunkNum)->each(function ($chunk) {
            $data = $chunk->map(function ($visit) {
                $visit->request   = json_encode($visit->request);
                $visit->languages = json_encode($visit->languages);
                $visit->headers   = json_encode($visit->headers);
                return $visit->toArray();
            })->toArray();

            //merged all the queries into one
            Visit::query()->insert($data);
        });
    }
}

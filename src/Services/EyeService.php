<?php


namespace Ami\Eye\Services;


use Exception;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;

class EyeService
{
    private $command = 'eye:record';

    public
        $ip,
        $class,
        $browser,
        $page_id,
        $time,
        $count,
        $collect,
        $get,
        $eye_cache;


    /**
     * EyeService constructor.
     */
    public function __construct()
    {
        $this->ip = request()->ip();
        $this->time = Carbon::now();
        $this->count = 0;
        $this->browser = $_SERVER['HTTP_USER_AGENT'] ?? NULL;
    }



    /**
     * Use This in Every Page that You Want to Set Cache
     * returns count of users and count of load in page
     * @param string $cache_label
     * @param int $id
     * @return stdClass
     */
    public function setAndGetViews(string $cache_name, $id = 0)
    {
        $views = new stdClass();
        $page_views = $this->set_cache_views($cache_name, $id);
        $views->users = $page_views->count_users();
        $views->seen = $page_views->count_views();
        return $views;
    }

    /**
     * @return false|string
     */
    public function readyTotalChart($type = "total", $json = true)
    {

        $view = $this->db_total();

        if (gettype($type) == 'array') {
            $view = $view->whereIn('type', $type);
        } elseif (gettype($type) == 'string') {
            $view = $view->where('type', $type);
        }


        $grouped = $view->orderBy('id', 'desc')->get();


        //  Grouping by Recorded Time (Created_at)
        $views = $this->groupByTime($grouped);


        if ($json === true)
            $views = json_encode($views);


        return $views;
    }

    /**
     * @param $type
     * @param int $page_id
     * @param bool $json
     * @return array|false|string
     */
    public function readyDetailsChart($type, $page_id = 0, $json = true)
    {

        $view = $this->db_details();

        if (gettype($type) == 'array') {
            $view = $view->whereIn('page_type', $type);
        } elseif (gettype($type) == 'string') {
            $view = $view->where('page_type', $type)->where('page_id', $page_id);
        }

        $grouped = $view->orderBy('id', 'desc')->get();

        //  Grouping by Recorded Time (Created_at)
        $views = $this->groupByTime($grouped);

        if ($json === true)
            $views = json_encode($views);

        return $views;
    }

    
    /**
     * Record in Database with CronJob >> app\Console\Commands\DailyViews
     */
    public function record()
    {
        Log::info('[---------- CRON JOB IS STARTED ----------]');


        try {
            if ($this->getCaches()->get()) {

                $prepared = $this->prepare_for_database();

                if (isset($prepared['total'])) {
                    Log::info('** Total Visits Has Been Set **');
                    $this->db_total()->insert($prepared['total']);
                }

                if (isset($prepared['detail'])) {
                    Log::info('** Detail Visits Has Been Set **');
                    $this->db_details()->insert($prepared['detail']);
                }
                $cache_names = $this->getCacheNames();
                $this->cacheForget($cache_names);
            }
        }
        catch (Exception $e) {
            Log::info($e->getMessage());
        }

        Log::info('[---------- CRON JOB IS FINISHED ----------]');
        echo "done";
    }

    public function getCommand()
    {
        return $this->command;
    }



/**
 * !----------------------------------------------------------------------------------
 * !----------------------------------------------------------------------------------
 * !----------------------------------------------------------------------------------
 * !----------------------------------------------------------------------------------
 * !----------------------------------------------------------------------------------
 * !----------------------------------------------------------------------------------
 * !----------------------------------------------------------------------------------
 * !----------------------------------------------------------------------------------
 */



    /**
     * @param $ip
     * @param $browser
     * @param $page_id
     * @param $class
     * @param $time
     * @param int $count
     * @return array
     */
    private function setView_array($ip, $browser, $page_id, $class, $time, $count = 0)
    {
        return [
            'ip' => $ip,
            'user_agent' => $browser,
            'page_id' => $page_id,
            'page_type' => $class,
            'visited_at' => $time,
            'count' => 1 + $count,
        ];
    }

    /**
     * @param $name
     * @return mixed|string
     */
    private function searchType($name)
    {
        return ($this->getTypes())[$name];
    }

    /**
     * Set and Get cache for every Page Visiting
     * @param string $cache_label
     * @param int|string $id
     * @return EyeService
     * @throws InvalidArgumentException
     */
    private function set_cache_views(string $cache_label, $id = 0)
    {
        try{
            $this->class = $this->type($cache_label);
            $this->page_id = $id;
            $page_views = [];
            if (cache()->has($cache_label)) {
                $page_views = cache($cache_label);
                $forget = Cache::forget($cache_label);
            }

            if (isset($page_views[$this->ip])) {
                $user_cache = $page_views[$this->ip];
                if (isset($page_views[$this->ip][$this->page_id])) {
                    $page_cache = $user_cache[$this->page_id];
                    $this->count = $page_cache['count'];
                    unset($page_cache);
                }
            }
            else {
                $page_views[$this->ip] = [];
            }

            $user_cache[$this->page_id] = self::setView_array($this->ip, $this->browser, $this->page_id, $this->class, $this->time, $this->count);
            $page_views[$this->ip] = $user_cache;

            Cache::forever($cache_label, collect($page_views));
            $this->collect = collect($page_views);

            $this->get = $this->collect;
        }
        catch (Exception $e){
            Log::info($e->getMessage());
        }
        return $this;

    }

    /**
     * List every cache given in one Collection
     * @param array|null $type_names
     * @return EyeService
     * @throws Exception
     */
    private function getCaches(array $type_names = null)
    {
        try{
            if (!$type_names)
                $type_names = self::getPages();
            $cache = [];
            foreach ($type_names as $page) {
                if (cache("{$page}"))
                    $cache["{$page}"] = cache("{$page}");
            }

            $this->eye_cache = collect($cache);

            $this->get = $this->eye_cache;
            return $this;
        }
        catch (Exception $e){
            Log::info($e->getMessage());
        }
    }

    /**
     * @return array
     */
    private function getPages()
    {
        return array_keys(config('eye.cache_types'));
    }

    /**
     * Combine $cache_name and $types
     * @return array
     */
    private function getCacheNames()
    {
        try{
            $cache_names = [];
            foreach ($this->getPages() as $type) {
                $cache_names[] = $type;
            }
            return $cache_names;
        }
        catch (Exception $e){
            Log::info($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    private function count_users()
    {
        return $this->collect->count();
    }

    /**
     * @return float|int
     */
    private function count_views()
    {
        return $this->collect->collapse()->sum('count');
    }

    /**
     **
     * @param null $getTypes_key
     * @return string
     */
    private function type($getTypes_key = null)
    {
        $key = $getTypes_key;
        $types = config('eye.cache_types');
        if ($types) {
            if (isset($types[$key]))
                return $types[$key];
        }
        return "unknown";
    }

    /**
     * Get Property Getter
     * @return mixed
     */
    private function get()
    {
        return $this->get;
    }

    /**
     ** Returns two arrays for inserting into DB_Tables by CronJob
     * @return array
     * @throws Exception
     */
    private function prepare_for_database()
    {
        try{
            $time = Carbon::now();


            $total_views = $detailed_views = $total = [];
            $groupPeople = collect([]);
            $caches = $this->getCaches()->eye_cache;

            if ($caches) {

    //            $groups = $caches->keys()->map(function ($value) {
    //                return $this->findGroupParent($value);
    //            })->unique()->flip();

                $total_page_count = $caches->sum(function ($ids) {
                    return $ids->sum(function ($value) {
                        return collect($value)->sum('count');
                    });
                });

                foreach ($caches as $type => $getCache) {

                    // cache name => ips => ids => details

                    ///////===========---------------Detailed-Views
                    /**
                     * IDs of pages
                     */
                    $cache_ids = $getCache->map(function ($ids) {
                        return array_keys($ids);
                    })->collapse()->countBy();


                    foreach ($getCache->collapse()->groupBy('page_id') as $id => $details) {
                        $detailed_views[] = [
                            'page_id' => $id,
                            'page_type' => $this->type($type),
                            'user_count' => $cache_ids[$id],
                            'page_count' => $details->sum('count'),
                            'created_at' => $time,
                        ];
                    }

                    $groupType = $this->findGroupParent($type);
                    ///////===========---------------Total-Views
                    $typeCount = [];

                    foreach ($getCache as $ip => $ids) {

                        $count = collect($ids)->sum('count');

                        $typeCount[] = $count;


                        if ($groupType) {

                            if (isset($groupPeople[$groupType])) {

                                if (isset($groupPeople[$groupType]["page_count"])) {

                                    $page_count = collect($groupPeople[$groupType])["page_count"];
                                    $page_count += $count;
                                    $groupPeople[$groupType] = ["page_count" => $page_count];

                                }
                                else {


                                    $groupPeople[$groupType] = collect($groupPeople[$groupType]);
                                    $groupPeople[$groupType]->page_count = $count;

                                }

                            } else {

                                $groupPeople[$groupType] = ["page_count" => $count];

                            }

                        }


                        $total[] = [
                            $ip => $count,
                        ];

                    }




                    /**
                     * Visits of Each Cache
                     */
                    $total_views[] = [
                        'type' => $type,
                        'user_count' => $getCache->count(),
                        'page_count' => array_sum($typeCount),
                        'created_at' => $time,
                    ];


                    $groupType = $this->findGroupParent($type);
                    if (isset($merged[$groupType])) {
                        $merged[$groupType] = $merged[$groupType]->merge($getCache);
                    } else {
                        $merged[$groupType] = $getCache;
                    }

                }

                $groupedCount = collect($merged)->map(function ($value, $key) use ($groupPeople , $time) {
                    return [
                        'type'       => $key,
                        'user_count' => $value->unique()->count(),
                        'page_count' => $groupPeople[$key]['page_count'],
                        'created_at' => $time,
                    ];
                })->values()->toArray();



                /**
                 * All Visited People
                 */
                $people = [];
                foreach ($total as $detail) {
                    $ip = key($detail);
                    if (!isset($people[$ip]))
                        $people[$ip] = $detail[$ip];
                    else
                        $people[$ip] += $detail[$ip];
                }

                /**
                 * All Visits of All Page Combined
                 */
                $total_page_count = $caches->sum(function ($ids) {
                    return $ids->sum(function ($value) {
                        return collect($value)->sum('count');
                    });
                });

                $total_users = [
                    'type' => 'total',
                    'user_count' => count($people),
                    'page_count' => $total_page_count, //array_sum($people)
                    'created_at' => $time,
                ];

                if (isset($total_views)) {
                    array_push($total_views, $total_users);
                    $total_views = array_merge($total_views, $groupedCount);
                }

            }

            return [
                'total' => $total_views ?? [],
                'detail' => $detailed_views ?? [],
            ];
        }
        catch (Exception $e){
            Log::info($e->getMessage());
        }
    }

    /**
     * @param string $type
     * @return int|string|null
     */
    private function findGroupParent(string $type)
    {
        foreach (config('eye.type_groups') as $key => $group) {

            if (in_array($type, $group) && $type != $key)
                return $key;
        }
        return null;
    }

    /**
     * @return Builder
     */
    private function db_total()
    {
        return DB::table(config('eye.tables.total'));
    }

    /**
     * @return Builder
     */
    private function db_details()
    {
        return DB::table(config('eye.tables.details'));
    }

    /**
     * @param $grouped
     * @return array
     */
    private function groupByTime($grouped)
    {

        $grouped = $grouped->groupBy(function ($date) {
            return \Illuminate\Support\Carbon::parse($date->created_at)->format('Y/m/d'); // grouping by years
        })->take(30);
        $total_views = [];
        foreach ($grouped as $key => $value) {

            /**
             * Total Views of Types
             */
            $total_views[] = [
                'user_count' => $value->sum('user_count'),
                'page_count' => $value->sum('page_count'),
                'date' => jdate($key)->format('%d %B'),
            ];

        }
        return $total_views;

    }

    /**
     * @param array $names
     */
    public function cacheForget(array $names){

        try{
            foreach ($names as $name) {
                Cache::forget($name);
            }
        }
        catch (Exception $e){
            Log::info($e->getMessage());
        }


    }


}

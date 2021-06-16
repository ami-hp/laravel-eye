<?php


namespace Ami\Eye\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * Class Eye
 * @package App\Services
 * @property static command
 * @method static getCaches(array $types = null)
 * @method static setView_array(string|null $ip, mixed $browser, int $page_id, false|string $class, \Carbon\Carbon $time, int $count)
 * @method static count_users()
 * @method static count_views()
 * @method static record()
 * @method static set_cache_views(string $string , int $id = 0)
 * @method static setAndGetViews(string $string , int $id = 0)
 * @method static readyTotalChart(string|array $type = "total" , Boolean $json = true)
 * @method static readyDetailsChart(string|array $type = "total" , int|string $page_id = 0 , Boolean $json = true)
 * @method static getCommand()
 */
class Eye extends Facade
{

    /**
     * Eye Reference
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        /**
         * ! Requirements -----
         * Carbon\Carbon
         * Illuminate\Database
         * Illuminate\Support
         * Illuminate\Console
         */
        return 'eyeService';
    }

}

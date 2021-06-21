<?php


namespace Ami\Eye\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * Class Eye
 * @package App\Services
 * @method static record()
 * @method static prepare_for_database()
 * @method static watch(string $cache_name ,  int $id = 0)
 * @method static readyTotalChart(string|array $type = "total" , $timeType = "gregorian",  Boolean $json = true)
 * @method static readyDetailsChart(string|array $type = "total" , int|string $page_id = 0 , $timeType = "gregorian", Boolean $json = true)
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

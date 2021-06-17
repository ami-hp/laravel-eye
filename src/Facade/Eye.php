<?php


namespace Ami\Eye\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * Class Eye
 * @package App\Services
 * @property static command
 * @method static record()
 * @method static setAndGetViews(string $cache_name , int $id = 0)
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

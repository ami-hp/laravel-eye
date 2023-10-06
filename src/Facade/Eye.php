<?php


namespace Ami\Eye\Facade;

use Illuminate\Support\Facades\Facade;


/**
 * Eye Facade
 * -------------
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
        return 'ami-visit-cacher';
    }

}

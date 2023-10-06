<?php

use Ami\Eye\Services\EyeService;

if (!function_exists('eye')) {
    /**
     * Access visitor through helper.
     *
     * @return EyeService
     */
    function eye()
    {
        return app('ami-visit-cacher');
    }
}

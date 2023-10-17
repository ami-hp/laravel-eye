<?php

use Ami\Eye\Services\EyeService;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('eye')) {
    /**
     * Access visitor through helper.
     *
     * @return EyeService
     * @throws Exception
     */
    function eye(?Model $visitable = null): EyeService
    {
        return new EyeService($visitable);
    }
}

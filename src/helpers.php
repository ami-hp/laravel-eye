<?php

use Ami\Eye\Services\EyeService;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('eye')) {
    /**
     * Access visitor through helper.
     *
     * @return EyeService
     */
    function eye(?Model $visitable = null): EyeService
    {
        $eye = new EyeService();

        if($visitable){
            $eye->setVisitable($visitable);
        }

        return $eye;
    }
}

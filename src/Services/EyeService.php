<?php


namespace Ami\Eye\Services;

use Ami\Eye\Traits\DataPreparation;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;


class EyeService
{

    use DataPreparation;

    /**
     * @return Cacher
     */
    public function cache(): Cacher
    {
        return new Cacher($this);
    }


}
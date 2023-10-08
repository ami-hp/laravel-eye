<?php


namespace Ami\Eye\Services;

use Ami\Eye\Traits\DataPreparation;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;


class EyeService
{

    use DataPreparation;

    /**
     * @throws BindingResolutionException
     */
    public function cacher()
    {
        return Container::getInstance()->make(Cacher::class);
    }

}
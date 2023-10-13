<?php


namespace Ami\Eye\Services;

use Ami\Eye\Traits\CrawlerDetection;
use Ami\Eye\Traits\DataPreparation;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;


class EyeService
{

    use DataPreparation;
    use CrawlerDetection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->initializeDataPrepTrait();
        $this->initializeCrawlerTrait();
    }

    /**
     * @return Cacher
     */
    public function viaDatabase(): Databaser
    {
        return new Databaser($this);
    }

    /**
     * @return Cacher
     */
    public function viaCache(): Cacher
    {
        return new Cacher($this);
    }


}
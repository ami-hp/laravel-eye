<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Contracts\UserAgentParser;
use Ami\Eye\Parsers\JenssegersAgent;
use Ami\Eye\Parsers\UAParser;
use Ami\Eye\Models\Visit;
use Ami\Eye\Services\EyeService;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use ReflectionClass;

trait CrawlerDetection
{
    /**
     * @var CrawlerDetect
     */
    public $detector;

    /**
     * @throws BindingResolutionException
     */
    protected function initializeCrawlerTrait()
    {
        $req = Container::getInstance()->make('request');

        $this->detector = new CrawlerDetect(
            $req->headers->all(),
            $req->server('HTTP_USER_AGENT')
        );
    }

    /**
     * Detects if the user is a robot
     * @return bool
     */
    public function isCrawler(): bool
    {
        return $this->detector->isCrawler();
    }

}
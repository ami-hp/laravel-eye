<?php

declare(strict_types=1);

namespace Ami\Eye\Support;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

class CrawlerDetectAdapter
{
    private $detector;

    public function __construct(CrawlerDetect $detector)
    {
        $this->detector = $detector;
    }

    /**
     * Determine if the current visitor is a crawler.
     */
    public function isCrawler(): bool
    {
        return $this->detector->isCrawler();
    }
}

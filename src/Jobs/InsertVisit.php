<?php

namespace Ami\Eye\Jobs;

use Ami\Eye\Services\Databaser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InsertVisit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $visit;

    protected $visitor = null;

    protected $visitable = null;

    protected $once = false;

    public function __construct(array $visit, $visitable, $visitor, $once)
    {
        $this->once      = $once;
        $this->visit     = $visit;
        $this->visitor   = $visitor;
        $this->visitable = $visitable;
    }

    public function handle()
    {
        Databaser::insert(
            $this->visit ,
            $this->visitable ,
            $this->visitor ,
            $this->once
        );
    }

}
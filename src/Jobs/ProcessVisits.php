<?php

namespace Ami\Eye\Jobs;

use Ami\Eye\Models\Visit;
use Ami\Eye\Services\Cacher;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessVisits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $visits;
    protected $chunkSize;

    public function __construct($visits , $chunkSize = 1000)
    {
        $this->visits    = $visits;
        $this->chunkSize = $chunkSize;
    }

    public function handle()
    {

        Cacher::insert($this->visits , $this->chunkSize);

    }

}
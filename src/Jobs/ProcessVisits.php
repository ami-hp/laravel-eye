<?php

namespace Ami\Eye\Jobs;

use Ami\Eye\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVisits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $visits;
    protected $chunkSize;

    public function __construct($visits , $chunkSize = 1000)
    {
        $this->visits = $visits;
        $this->chunkSize = $chunkSize;
    }

    public function handle()
    {

        $this->visits->chunk($this->chunkSize)->each(function ($chunk) {
            $data = $chunk->map(function ($visit) {
                return $visit->toArray();
            })->toArray();

            Visit::query()->insert($data);
        });

    }
}
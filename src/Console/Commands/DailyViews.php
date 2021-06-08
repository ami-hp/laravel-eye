<?php

namespace Ami\Eye\Console\Commands;

use Exception;
use Illuminate\Console\Command;


class DailyViews extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     * ?----------------------------------------------------
     * !===================================================
     * * For using this Command,
     * * You Can Add This Property to App\Console\Kernel
     * * protected $commands = [Commands\DailyViews::class];
     * !===================================================
     * ?----------------------------------------------------
     */
    protected $signature = 'eye:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Views in Every Page Recorded by Cache, Then Clear Cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        \Ami\Eye\Facade\Eye::record();
    }
}

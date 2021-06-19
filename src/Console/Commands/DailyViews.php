<?php

namespace App\Console\Commands;

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
     * * You Can Add This Property to App\Console\Kernel
     * * protected $commands = [Commands\DailyViews::class];
     * * Or
     * * Add this to Your Cron Job, Example:
     * ? Cpanel >> CronJobs >>  /usr/local/bin/php /home/user-name/project-path/artisan eye:record > /dev/null 2>&1
     * !===================================================
     * ?----------------------------------------------------
     */
    protected $signature = "eye:record";

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
        if(class_exists("\Ami\Eye\Facade\Eye"))
            \Ami\Eye\Facade\Eye::record();
    }
}

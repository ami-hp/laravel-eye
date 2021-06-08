<?php

namespace Ami\Eye\Providers;

use Ami\Eye\Services\EyeService;
use Illuminate\Support\ServiceProvider;

class EyeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('eyeService' , function () {
            return new EyeService();
        });

        $this->mergeConfigFrom(__DIR__ . '/config/eye.php' , 'eye');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('/migrations'),
        ] , 'migration');

        $this->publishes([
            __DIR__ . '/config/eye.php' => config_path('/eye.php'),
        ] , 'config');
    }
}

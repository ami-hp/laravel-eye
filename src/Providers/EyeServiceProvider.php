<?php

namespace Ami\Eye\Providers;

use Ami\Eye\Services\EyeService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class EyeServiceProvider extends ServiceProvider
{
    const FACADE_NAME = 'ami-visit-cacher';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(self::FACADE_NAME , function () {
            return new EyeService();
        });

        $this->mergeConfigFrom(__DIR__ . '/../../config/eye.php' , 'eye');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        if (! class_exists('CreateVisitsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../../database/migrations/create_visits_table.php.stub' => database_path("/migrations/{$timestamp}_create_visits_table.php"),
            ], 'migrations');
        }

//        $this->publishes([
//            __DIR__ . '/database/migrations' => database_path("/migrations/"),
//        ] , 'migration');

        $this->publishes([
            __DIR__ . '/../../config/eye.php' => config_path('/eye.php'),
        ] , 'config');

        $this->registerMacroHelpers();
    }

    /**
     * Register micros
     */
    protected function registerMacroHelpers()
    {
        Request::macro('visitor', function () {
            return app(self::FACADE_NAME);
        });
    }
}

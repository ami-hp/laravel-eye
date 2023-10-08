<?php

namespace Ami\Eye\Providers;

use Ami\Eye\Services\EyeService;
use Ami\Eye\Support\CrawlerDetectAdapter;
use Ami\Eye\Support\Period;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

/**
 * @method where($column , $operator , $value = null)
 * @method whereBetween($column , $value)
 */
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

        $this->app->bind(CrawlerDetectAdapter::class, function ($app) {
            $detector = new CrawlerDetect(
                $app['request']->headers->all(),
                $app['request']->server('HTTP_USER_AGENT')
            );

            return new CrawlerDetectAdapter($detector);
        });
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
        Collection::macro('whereVisitor', function (Model $user) {
            return $this->where('visitor_id' , $user->id)->where('visitor_type' , get_class($user));
        });

        Collection::macro('whereVisitable', function (Model $post) {
            return $this->where('visitor_id' , $post->id)->where('visitor_type' , get_class($post));
        });
        
        Collection::macro('period', function (Period $period) {

            $startDateTime = $period->getStartDateTime();
            $endDateTime   = $period->getEndDateTime();

            if ($startDateTime !== null && $endDateTime === null) {
                $this->where('viewed_at', '>=', $startDateTime);
            } elseif ($startDateTime === null && $endDateTime !== null) {
                $this->where('viewed_at', '<=', $endDateTime);
            } elseif ($startDateTime !== null && $endDateTime !== null) {
                $this->whereBetween('viewed_at', [$startDateTime, $endDateTime]);
            }
            return $this;
        });

    }
}

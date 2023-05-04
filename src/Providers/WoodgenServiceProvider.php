<?php

namespace Treii28\Grampsdb\Providers;

//use App\Models\Helpers\WoodgenHelper;
use Illuminate\Support\ServiceProvider;

class WoodgenServiceProvider extends ServiceProvider
{
    const VERSION = '0.0.1';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //$this->mergeConfigFrom(config_path(), 'woodgen' );
        /*
        $this->app->singleton('woodgenhelper', function ($app) {
            //$config = $app->make('config')->get('woodgen');

            return new WoodgenHelper(); //($config);
        });
         */
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

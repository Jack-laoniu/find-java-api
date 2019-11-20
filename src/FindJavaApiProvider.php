<?php

namespace SelfTools\FindJavaApi;

use Illuminate\Support\ServiceProvider;

class FindJavaApiProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/piplines.php' => config_path('piplines.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('FindJavaApi',function (){
            return new FindJavaLogic();
        });
    }
}

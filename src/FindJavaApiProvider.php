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
        //
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

<?php

namespace VocoLabs\RollNumber;

use Illuminate\Support\ServiceProvider;

class RollNumberServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    public function register()
    {
        //
    }
}

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
        $this->registerCommands();
        $this->registerPublishing();
        $this->registerMigrations();
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'roll-number-migrations');

            $this->publishes([
                __DIR__ . '/../stubs/RollNumberServiceProvider.stub' => app_path('Providers/RollNumberServiceProvider.php'),
            ], 'roll-number-provider');
        }
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ClearCommand::class,
                Console\InstallCommand::class,
                Console\PauseCommand::class,
                Console\PruneCommand::class,
                Console\PublishCommand::class,
                Console\ResumeCommand::class,
            ]);
        }
    }
}

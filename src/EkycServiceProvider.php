<?php

namespace Fintech\Ekyc;

use Fintech\Ekyc\Commands\EkycCommand;
use Fintech\Ekyc\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

class EkycServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ekyc.php', 'fintech.ekyc'
        );

        $this->app->register(RouteServiceProvider::class);

        $this->app->register(RepositoryServiceProvider::class);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/ekyc.php' => config_path('fintech/ekyc.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'ekyc');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/ekyc'),
        ]);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ekyc');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/ekyc'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                EkycCommand::class,
            ]);
        }
    }
}

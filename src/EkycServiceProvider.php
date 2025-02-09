<?php

namespace Fintech\Ekyc;

use Fintech\Core\Traits\Packages\RegisterPackageTrait;
use Fintech\Ekyc\Commands\InstallCommand;
use Fintech\Ekyc\Providers\RepositoryServiceProvider;
use Illuminate\Support\ServiceProvider;

class EkycServiceProvider extends ServiceProvider
{
    use RegisterPackageTrait;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->packageCode = 'ekyc';

        $this->mergeConfigFrom(
            __DIR__.'/../config/ekyc.php', 'fintech.ekyc'
        );

        $this->app->register(RepositoryServiceProvider::class);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->injectOnConfig();

        $this->publishes([
            __DIR__.'/../config/ekyc.php' => config_path('fintech/ekyc.php'),
        ], 'fintech-ekyc-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'ekyc');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/ekyc'),
        ], 'fintech-ekyc-lang');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ekyc');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/ekyc'),
        ], 'fintech-ekyc-views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}

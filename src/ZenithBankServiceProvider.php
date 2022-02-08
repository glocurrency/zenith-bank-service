<?php

namespace GloCurrency\ZenithBank;

use Illuminate\Support\ServiceProvider;
use GloCurrency\ZenithBank\Config;
use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;

class ZenithBankServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->bindConfig();
    }

    /**
     * Setup the configuration for ZenithBank.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/zenith_bank.php', 'services.zenith_bank'
        );
    }

    /**
     * Bind the ZenithBank logger interface to the ZenithBank logger.
     *
     * @return void
     */
    protected function bindConfig()
    {
        $this->app->bind(ConfigInterface::class, Config::class);
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (ZenithBank::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/zenith_bank.php' => $this->app->configPath('zenith_bank.php'),
            ], 'zenith-bank-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'zenith-bank-migrations');
        }
    }
}

<?php

namespace MahdiHejazi\LaravelGhasedakSms;

use Illuminate\Support\ServiceProvider;
use MahdiHejazi\LaravelGhasedakSms\Services\GhasedakSmsService;

class GhasedakSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ghasedak.php',
            'ghasedak'
        );

        $this->app->singleton('ghasedak-sms', function ($app) {
            return new GhasedakSmsService();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publish config file
            $this->publishes([
                __DIR__ . '/../config/ghasedak.php' => config_path('ghasedak.php'),
            ], 'ghasedak-config');

            // Publish language files
            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/ghasedak'),
            ], 'ghasedak-lang');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'ghasedak');
    }

    public function provides()
    {
        return ['ghasedak-sms'];
    }
}

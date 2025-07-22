<?php

namespace MahdiHejazi\LaravelGhasedakSms\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use MahdiHejazi\LaravelGhasedakSms\GhasedakSmsServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('ghasedak.api_key', 'test-api-key');
        config()->set('ghasedak.sender', '10008566');
        config()->set('ghasedak.templates.phoneVerifyCode', 'test-template');
    }

    protected function getPackageProviders($app)
    {
        return [
            GhasedakSmsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('ghasedak.logging.enabled', false);
    }
}

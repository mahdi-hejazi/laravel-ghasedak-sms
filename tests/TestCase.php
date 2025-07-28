<?php

namespace MahdiHejazi\LaravelGhasedakSms\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use MahdiHejazi\LaravelGhasedakSms\GhasedakSmsServiceProvider;
use Dotenv\Dotenv;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        $this->loadTestingEnvironment();

        parent::setUp();

        config()->set('ghasedak.api_key', env('GHASEDAK_API_KEY', 'test-api-key'));
        config()->set('ghasedak.sender', env('GHASEDAK_SENDER', '10008566'));
        config()->set('ghasedak.templates.phoneVerifyCode', env('GHASEDAK_TEMPLATE_NAME', 'test-template'));
        config()->set('ghasedak.templates.orderCreated', 'order-template');
        config()->set('ghasedak.templates.orderConfirmed', 'order-confirmed');
        config()->set('ghasedak.templates.test_template', env('GHASEDAK_TEMPLATE_NAME', 'test-template'));
        config()->set('ghasedak.api.otp_url', 'https://gateway.ghasedak.me/rest/api/v1/WebService/SendOtpWithParams');
        config()->set('ghasedak.api.simple_url', 'https://gateway.ghasedak.me/rest/api/v1/WebService/SendSingleSMS');
        config()->set('ghasedak.api.account_info_url', 'https://gateway.ghasedak.me/rest/api/v1/WebService/GetAccountInformation');
        config()->set('ghasedak.logging.enabled', env('GHASEDAK_LOGGING', false));
    }

    protected function loadTestingEnvironment(): void
    {
        $testingEnvFile = __DIR__ . '/../.env.testing';

        if (file_exists($testingEnvFile)) {
            $dotenv = Dotenv::createImmutable(dirname($testingEnvFile), '.env.testing');
            $dotenv->load();
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            GhasedakSmsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'GhasedakSms' => \MahdiHejazi\LaravelGhasedakSms\Facades\GhasedakSms::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup the database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Helper method to check if real API credentials are available
     */
    protected function hasRealApiCredentials(): bool
    {
        $required = [
            'GHASEDAK_API_KEY',
            'GHASEDAK_TEST_PHONE',
            'GHASEDAK_TEMPLATE_NAME',
            'GHASEDAK_TEMPLATE_PARAM1'
        ];

        foreach ($required as $key) {
            if (!env($key) || env($key) === 'test-api-key') {
                return false;
            }
        }

        return true;
    }

    /**
     * Skip test if real API credentials are not configured
     */
    protected function skipIfNoRealApiConfig(): void
    {
        if (!$this->hasRealApiCredentials()) {
            $this->markTestSkipped('Real API credentials not configured in .env.testing');
        }
    }
}

<?php

namespace MahdiHejazi\LaravelGhasedakSms\Tests\Feature;

use MahdiHejazi\LaravelGhasedakSms\Tests\TestCase;
use MahdiHejazi\LaravelGhasedakSms\Notifications\SendSmsNotification;
use MahdiHejazi\LaravelGhasedakSms\Notifications\SimpleSmsNotification;
use MahdiHejazi\LaravelGhasedakSms\Facades\GhasedakSms;
use MahdiHejazi\LaravelGhasedakSms\Exceptions\GhasedakSmsException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class GhasedakChannelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test config
        config([
            'ghasedak.api_key' => 'test-api-key',
            'ghasedak.sender' => '10008566',
            'ghasedak.templates.phoneVerifyCode' => 'test-template',
            'ghasedak.api.verify_url' => 'http://api.ghasedaksms.com/v2/send/verify',
            'ghasedak.api.simple_url' => 'http://api.ghasedaksms.com/v2/sms/send/simple',
            'ghasedak.logging.enabled' => false,
        ]);
    }

    /** @test */
    public function it_can_send_template_sms_with_mock_response()
    {
        // Mock successful API response
        Http::fake([
            'api.ghasedaksms.com/*' => Http::response([
                'result' => 'success',
                'messageids' => 12345678
            ], 200)
        ]);

        $response = GhasedakSms::sendVerificationCode('09123456789', '1234');

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['result']);
        $this->assertEquals(12345678, $response['messageids']);
    }

    /** @test */
    public function it_can_send_simple_sms_with_mock_response()
    {
        // Mock successful API response
        Http::fake([
            'api.ghasedaksms.com/*' => Http::response([
                'result' => 'success',
                'messageids' => 87654321
            ], 200)
        ]);

        $response = GhasedakSms::sendSimple('09123456789', 'Test message');

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['result']);
        $this->assertEquals(87654321, $response['messageids']);
    }

    /** @test */
    public function it_throws_exception_on_missing_api_key()
    {
        config(['ghasedak.api_key' => null]);

        $this->expectException(GhasedakSmsException::class);
        $this->expectExceptionMessage('کلید API قاصدک تنظیم نشده است');

        GhasedakSms::sendVerificationCode('09123456789', '1234');
    }

    /** @test */
    public function it_throws_exception_on_missing_template()
    {
        config(['ghasedak.templates.phoneVerifyCode' => '']);

        $this->expectException(GhasedakSmsException::class);

        GhasedakSms::sendVerificationCode('09123456789', '1234');
    }

    /** @test */
    public function it_handles_api_error_response()
    {
        // Mock error API response
        Http::fake([
            'api.ghasedaksms.com/*' => Http::response([
                'result' => 'error',
                'message' => 9
            ], 200)
        ]);

        $this->expectException(GhasedakSmsException::class);

        GhasedakSms::sendVerificationCode('09123456789', '1234');
    }

    /** @test */
    public function it_handles_http_error()
    {
        // Mock HTTP error
        Http::fake([
            'api.ghasedaksms.com/*' => Http::response([], 500)
        ]);

        $this->expectException(GhasedakSmsException::class);

        GhasedakSms::sendVerificationCode('09123456789', '1234');
    }

    /** @test */
    public function it_can_use_notification_facade()
    {
        Http::fake([
            'api.ghasedaksms.com/*' => Http::response([
                'result' => 'success',
                'messageids' => 11111111
            ], 200)
        ]);

        // This should not throw an exception
        Notification::route('sms', '09123456789')
            ->notify(SendSmsNotification::verificationCode('1234', '09123456789'));

        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_use_factory_methods()
    {
        Http::fake([
            'api.ghasedaksms.com/*' => Http::response([
                'result' => 'success',
                'messageids' => 22222222
            ], 200)
        ]);

        $response = GhasedakSms::sendOrderConfirmed('09123456789', 'ORD-123', '50000', '1403/10/15');

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['result']);
    }

    /** @test */
    public function it_validates_empty_message_for_simple_sms()
    {
        $this->expectException(GhasedakSmsException::class);

        GhasedakSms::sendSimple('09123456789', '');
    }

    /** @test */
    public function it_validates_empty_receptor()
    {
        $this->expectException(GhasedakSmsException::class);

        GhasedakSms::sendSimple('', 'Test message');
    }

    // ============================================
    // REAL API TESTS (Uncomment to test with real API)
    // ============================================

    /**
     * @test
     * @group integration
     * 
     * To run this test:
     * 1. Set real API key in .env: GHASEDAK_API_KEY=your_real_key
     * 2. Set real template: GHASEDAK_TEMPLATE_VERIFY_CODE=your_template_name
     * 3. Run: vendor/bin/phpunit --group integration
     */
    public function it_can_send_real_verification_sms()
    {
        // Skip if no real API key provided
        if (!env('GHASEDAK_API_KEY') || env('GHASEDAK_API_KEY') === 'test-api-key') {
            $this->markTestSkipped('Set GHASEDAK_API_KEY in .env to run real API tests');
        }

        // Use real config from .env
        config([
            'ghasedak.api_key' => env('GHASEDAK_API_KEY'),
            'ghasedak.templates.phoneVerifyCode' => env('GHASEDAK_TEMPLATE_VERIFY_CODE', 'your-template-name'),
            'ghasedak.logging.enabled' => true,
        ]);

        $response = GhasedakSms::sendVerificationCode('09123456789', '1234');

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['result']);
        $this->assertGreaterThan(1000, $response['messageids']);
    }

    /**
     * @test
     * @group integration
     * 
     * To run: vendor/bin/phpunit --group integration
     */
    public function it_can_send_real_simple_sms()
    {
        if (!env('GHASEDAK_API_KEY') || env('GHASEDAK_API_KEY') === 'test-api-key') {
            $this->markTestSkipped('Set GHASEDAK_API_KEY in .env to run real API tests');
        }

        config([
            'ghasedak.api_key' => env('GHASEDAK_API_KEY'),
            'ghasedak.logging.enabled' => true,
        ]);

        $response = GhasedakSms::sendSimple('09123456789', 'Test message from Laravel package');

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['result']);
        $this->assertGreaterThan(1000, $response['messageids']);
    }
}

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

        // Set up test config with NEW API URLs
        config([
            'ghasedak.api_key' => 'test-api-key',
            'ghasedak.sender' => '10008566',
            'ghasedak.templates.phoneVerifyCode' => 'test-template',
            'ghasedak.api.verify_url' => 'https://gateway.ghasedak.me/rest/api/v1/WebService/SendOtpWithParams',
            'ghasedak.api.simple_url' => 'https://gateway.ghasedak.me/rest/api/v1/WebService/SendSingleSMS',
            'ghasedak.logging.enabled' => false,
        ]);
    }

    /** @test */
    public function it_can_send_template_sms_with_mock_response()
    {
        // Mock successful API response with NEW structure
        Http::fake([
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => true,
                'StatusCode' => 200,
                'Message' => 'با موفقیت انجام شد',
                'Data' => [
                    'LineNumber' => '10002000200101',
                    'MessageBody' => 'کد تایید شما 1234',
                    'Items' => [
                        [
                            'Receptor' => '09123456789',
                            'Cost' => 940,
                            'MessageId' => 12345678,
                            'SendDate' => '2024-07-04T06:20:10.856Z',
                        ]
                    ],
                    'Cost' => 940
                ]
            ], 200)
        ]);

        $response = GhasedakSms::sendVerificationCode('09123456789', '1234');

        $this->assertIsArray($response);
        $this->assertTrue($response['IsSuccess']);
        $this->assertEquals(200, $response['StatusCode']);
        $this->assertEquals(12345678, $response['Data']['Items'][0]['MessageId']);
    }

    /** @test */
    public function it_can_send_simple_sms_with_mock_response()
    {
        // Mock successful API response with NEW structure
        Http::fake([
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => true,
                'StatusCode' => 200,
                'Message' => 'با موفقیت انجام شد',
                'Data' => [
                    'Receptor' => '09123456789',
                    'LineNumber' => '210002100',
                    'Cost' => 1732,
                    'MessageId' => 87654321,
                    'Message' => 'Test message',
                    'SendDate' => '2024-07-09T14:01:28.4277539+03:30'
                ]
            ], 200)
        ]);

        $response = GhasedakSms::sendSimple('09123456789', 'Test message');

        $this->assertIsArray($response);
        $this->assertTrue($response['IsSuccess']);
        $this->assertEquals(87654321, $response['Data']['MessageId']);
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
        // Mock error API response with NEW structure
        Http::fake([
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => false,
                'StatusCode' => 418,
                'Message' => 'اعتبار شما کافی نمی‌باشد'
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
            'gateway.ghasedak.me/*' => Http::response([], 500)
        ]);

        $this->expectException(GhasedakSmsException::class);

        GhasedakSms::sendVerificationCode('09123456789', '1234');
    }

    /** @test */
    public function it_can_use_notification_facade()
    {
        Http::fake([
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => true,
                'StatusCode' => 200,
                'Message' => 'با موفقیت انجام شد',
                'Data' => [
                    'Items' => [
                        ['MessageId' => 11111111]
                    ]
                ]
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
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => true,
                'StatusCode' => 200,
                'Data' => [
                    'Items' => [
                        ['MessageId' => 22222222]
                    ]
                ]
            ], 200)
        ]);

        $response = GhasedakSms::sendOrderConfirmed('09123456789', 'ORD-123', '50000', '1403/10/15');

        $this->assertIsArray($response);
        $this->assertTrue($response['IsSuccess']);
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

    /** @test */
    public function it_supports_up_to_10_parameters()
    {
        Http::fake([
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => true,
                'StatusCode' => 200,
                'Data' => [
                    'Items' => [
                        ['MessageId' => 33333333]
                    ]
                ]
            ], 200)
        ]);

        // Test with 10 parameters
        $params = ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9', 'p10'];
        $response = GhasedakSms::sendTemplate('09123456789', 'phoneVerifyCode', $params);

        $this->assertIsArray($response);
        $this->assertTrue($response['IsSuccess']);
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
        $this->assertTrue($response['IsSuccess']);
        $this->assertGreaterThan(0, $response['Data']['Items'][0]['MessageId']);
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
        $this->assertTrue($response['IsSuccess']);
        $this->assertGreaterThan(0, $response['Data']['MessageId']);
    }
}
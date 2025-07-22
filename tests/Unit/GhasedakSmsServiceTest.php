<?php

namespace MahdiHejazi\LaravelGhasedakSms\Tests\Unit;

use MahdiHejazi\LaravelGhasedakSms\Tests\TestCase;
use MahdiHejazi\LaravelGhasedakSms\Services\GhasedakSmsService;
use Illuminate\Support\Facades\Http;

class GhasedakSmsServiceTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_service()
    {
        $service = new GhasedakSmsService();

        $this->assertInstanceOf(GhasedakSmsService::class, $service);
    }

    /** @test */
    public function it_can_send_verification_code()
    {
        Http::fake([
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => true,
                'StatusCode' => 200,
                'Data' => [
                    'Items' => [
                        ['MessageId' => 12345678]
                    ]
                ]
            ], 200)
        ]);

        $service = new GhasedakSmsService();
        $response = $service->sendVerificationCode('09123456789', '1234');

        $this->assertIsArray($response);
        $this->assertTrue($response['IsSuccess']);
    }

    /** @test */
    public function it_can_send_simple_message()
    {
        Http::fake([
            'gateway.ghasedak.me/*' => Http::response([
                'IsSuccess' => true,
                'StatusCode' => 200,
                'Data' => [
                    'MessageId' => 87654321
                ]
            ], 200)
        ]);

        $service = new GhasedakSmsService();
        $response = $service->sendSimple('09123456789', 'پیام تست');

        $this->assertIsArray($response);
        $this->assertTrue($response['IsSuccess']);
    }
}
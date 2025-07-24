<?php

namespace MahdiHejazi\LaravelGhasedakSms\Tests\Feature;

use MahdiHejazi\LaravelGhasedakSms\Tests\TestCase;
use MahdiHejazi\LaravelGhasedakSms\Facades\GhasedakSms;
use MahdiHejazi\LaravelGhasedakSms\Exceptions\GhasedakSmsException;

/**
 * Simple Feature Tests for Ghasedak SMS
 *
 * To run these tests with real API:
 * 1. Set these environment variables in your .env.testing file:
 *    - GHASEDAK_API_KEY=your_real_api_key
 *    - GHASEDAK_TEST_PHONE=09xxxxxxxxx (your test phone number)
 *    - GHASEDAK_TEMPLATE_NAME=your_template_name (template with one parameter)
 *    - GHASEDAK_TEMPLATE_PARAM1=test_value (the parameter value to send)
 *
 * 2. Run: vendor/bin/phpunit --group real-api
 */
class GhasedakChannelTest  extends TestCase
{
    protected function skipIfNoRealApiConfig(): void
    {
        $requiredVars = [
            'GHASEDAK_API_KEY',
            'GHASEDAK_TEST_PHONE',
            'GHASEDAK_TEMPLATE_NAME',
            'GHASEDAK_TEMPLATE_PARAM1'
        ];

        foreach ($requiredVars as $var) {
            if (!env($var)) {
                $this->markTestSkipped(
                    "Set {$var} in .env.testing to run real API tests. Required vars: " .
                    implode(', ', $requiredVars)
                );
            }
        }

        // Configure with real credentials
        config([
            'ghasedak.api_key' => env('GHASEDAK_API_KEY'),
            'ghasedak.sender' => env('GHASEDAK_SENDER', '10008566'),
            'ghasedak.templates.test_template' => env('GHASEDAK_TEMPLATE_NAME'),
            'ghasedak.logging.enabled' => true,
        ]);
    }

    /**
     * @test
     * @group real-api
     */
    public function it_can_send_template_sms_with_real_api()
    {
        $this->skipIfNoRealApiConfig();

        $phone = env('GHASEDAK_TEST_PHONE');
        $templateName = 'test_template';
        $param1 = env('GHASEDAK_TEMPLATE_PARAM1');

        try {
            $response = GhasedakSms::sendTemplate($phone, $templateName, [$param1]);

            // Assert successful response
            $this->assertIsArray($response);
            $this->assertTrue($response['IsSuccess'], 'Template SMS should be sent successfully');
            $this->assertEquals(200, $response['StatusCode']);
            $this->assertArrayHasKey('Data', $response);
            $this->assertArrayHasKey('Items', $response['Data']);
            $this->assertGreaterThan(0, $response['Data']['Items'][0]['MessageId']);

            echo "\n✅ Template SMS sent successfully!";
            echo "\n   Phone: {$phone}";
            echo "\n   Template: " . env('GHASEDAK_TEMPLATE_NAME');
            echo "\n   Parameter: {$param1}";
            echo "\n   MessageId: " . $response['Data']['Items'][0]['MessageId'];

        } catch (GhasedakSmsException $e) {
            $this->fail(
                "Template SMS failed. Error: " . $e->getMessage() .
                " (Code: " . $e->getErrorCode() . ")"
            );
        }
    }

    /**
     * @test
     * @group real-api
     */
    public function it_can_send_simple_sms_with_real_api()
    {
        $this->skipIfNoRealApiConfig();

        $phone = env('GHASEDAK_TEST_PHONE');
        $message = 'تست پکیج قاصدک لاراول - ' . now()->format('H:i:s');

        try {
            $response = GhasedakSms::sendSimple($phone, $message);

            // Assert successful response
            $this->assertIsArray($response);
            $this->assertTrue($response['IsSuccess'], 'Simple SMS should be sent successfully');
            $this->assertEquals(200, $response['StatusCode']);
            $this->assertArrayHasKey('Data', $response);
            $this->assertGreaterThan(0, $response['Data']['MessageId']);

            echo "\n✅ Simple SMS sent successfully!";
            echo "\n   Phone: {$phone}";
            echo "\n   Message: {$message}";
            echo "\n   MessageId: " . $response['Data']['MessageId'];

        } catch (GhasedakSmsException $e) {
            $this->fail(
                "Simple SMS failed. Error: " . $e->getMessage() .
                " (Code: " . $e->getErrorCode() . ")"
            );
        }
    }
}
<?php

namespace MahdiHejazi\LaravelGhasedakSms\Tests\Feature;

use MahdiHejazi\LaravelGhasedakSms\Tests\TestCase;
use MahdiHejazi\LaravelGhasedakSms\Facades\GhasedakSms;
use MahdiHejazi\LaravelGhasedakSms\Exceptions\GhasedakSmsException;

/**
 * Real API Tests for Ghasedak SMS
 *
 * These tests will use your .env.testing configuration:
 * - GHASEDAK_API_KEY=3069544b896f22532a350e0e21046f7af01c19d95854c053d94c16e22c3aad59
 * - GHASEDAK_TEST_PHONE=09910691698
 * - GHASEDAK_TEMPLATE_NAME=abzargheteVerifyCode2
 * - GHASEDAK_TEMPLATE_PARAM1=1234
 */
class GhasedakRealApiTest extends TestCase
{
    /**
     * @test
     * @group real-api
     */
    public function it_can_send_template_sms_with_real_api()
    {
        $this->skipIfNoRealApiConfig();

        $phone = env('GHASEDAK_TEST_PHONE');
        $templateName = 'test_template'; // Uses GHASEDAK_TEMPLATE_NAME from config
        $param1 = env('GHASEDAK_TEMPLATE_PARAM1');

        echo "\n🔧 Testing Template SMS with Real API:";
        echo "\n   API Key: " . substr(env('GHASEDAK_API_KEY'), 0, 10) . "...";
        echo "\n   Phone: {$phone}";
        echo "\n   Template: " . env('GHASEDAK_TEMPLATE_NAME');
        echo "\n   Parameter: {$param1}";

        try {
            $response = GhasedakSms::sendTemplate($phone, $templateName, [$param1]);

            // Assert successful response
            $this->assertIsArray($response);
            $this->assertTrue($response['IsSuccess'], 'Template SMS should be sent successfully');
            $this->assertEquals(200, $response['StatusCode']);
            $this->assertArrayHasKey('Data', $response);
            $this->assertArrayHasKey('Items', $response['Data']);
            $this->assertGreaterThan(0, $response['Data']['Items'][0]['MessageId']);

            echo "\n✅ SUCCESS! Template SMS sent successfully!";
            echo "\n   MessageId: " . $response['Data']['Items'][0]['MessageId'];
            echo "\n   Cost: " . ($response['Data']['Cost'] ?? 'N/A');
            echo "\n   LineNumber: " . ($response['Data']['LineNumber'] ?? 'N/A');

        } catch (GhasedakSmsException $e) {
            echo "\n❌ FAILED! Template SMS Error:";
            echo "\n   Error Code: " . $e->getErrorCode();
            echo "\n   Error Message: " . $e->getMessage();

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

        echo "\n🔧 Testing Simple SMS with Real API:";
        echo "\n   Phone: {$phone}";
        echo "\n   Message: {$message}";

        try {
            $response = GhasedakSms::sendSimple($phone, $message);

            // Assert successful response
            $this->assertIsArray($response);
            $this->assertTrue($response['IsSuccess'], 'Simple SMS should be sent successfully');
            $this->assertEquals(200, $response['StatusCode']);
            $this->assertArrayHasKey('Data', $response);
            $this->assertGreaterThan(0, $response['Data']['MessageId']);

            echo "\n✅ SUCCESS! Simple SMS sent successfully!";
            echo "\n   MessageId: " . $response['Data']['MessageId'];
            echo "\n   Cost: " . ($response['Data']['Cost'] ?? 'N/A');
            echo "\n   LineNumber: " . ($response['Data']['LineNumber'] ?? 'N/A');

        } catch (GhasedakSmsException $e) {
            echo "\n❌ FAILED! Simple SMS Error:";
            echo "\n   Error Code: " . $e->getErrorCode();
            echo "\n   Error Message: " . $e->getMessage();

            $this->fail(
                "Simple SMS failed. Error: " . $e->getMessage() .
                " (Code: " . $e->getErrorCode() . ")"
            );
        }
    }

    /**
     * @test
     * @group real-api
     */
    public function it_can_send_verification_code_with_real_api()
    {
        $this->skipIfNoRealApiConfig();

        $phone = env('GHASEDAK_TEST_PHONE');
        $code = env('GHASEDAK_TEMPLATE_PARAM1');

        echo "\n🔧 Testing Verification Code with Real API:";
        echo "\n   Phone: {$phone}";
        echo "\n   Code: {$code}";

        try {
            $response = GhasedakSms::sendVerificationCode($phone, $code);

            $this->assertIsArray($response);
            $this->assertTrue($response['IsSuccess']);
            $this->assertEquals(200, $response['StatusCode']);

            echo "\n✅ SUCCESS! Verification code sent!";
            echo "\n   MessageId: " . $response['Data']['Items'][0]['MessageId'];

        } catch (GhasedakSmsException $e) {
            echo "\n❌ FAILED! Verification Error:";
            echo "\n   Error Code: " . $e->getErrorCode();
            echo "\n   Error Message: " . $e->getMessage();

            $this->fail("Verification code failed: " . $e->getMessage());
        }
    }

    /**
     * @test
     * @group real-api-error
     */
    public function it_handles_invalid_phone_number_error()
    {
        $this->skipIfNoRealApiConfig();

        echo "\n🔧 Testing Invalid Phone Number Handling:";

        $this->expectException(GhasedakSmsException::class);

        // Test with invalid phone number
        GhasedakSms::sendVerificationCode('123456', '1234');
    }

    /**
     * @test
     * @group real-api-debug
     */
    public function it_can_test_api_connectivity()
    {
        $this->skipIfNoRealApiConfig();

        echo "\n🔧 Testing API Connectivity:";
        echo "\n   API Key: " . substr(env('GHASEDAK_API_KEY'), 0, 10) . "...";
        echo "\n   Verify URL: " . config('ghasedak.api.verify_url');
        echo "\n   Simple URL: " . config('ghasedak.api.simple_url');

        // This test just checks if we can instantiate the service without errors
        $this->assertTrue($this->hasRealApiCredentials());
        $this->assertEquals(env('GHASEDAK_API_KEY'), config('ghasedak.api_key'));
        $this->assertEquals(env('GHASEDAK_TEMPLATE_NAME'), config('ghasedak.templates.test_template'));

        echo "\n✅ API configuration loaded successfully!";
    }
}
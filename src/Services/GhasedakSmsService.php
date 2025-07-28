<?php

namespace MahdiHejazi\LaravelGhasedakSms\Services;

use MahdiHejazi\LaravelGhasedakSms\Notifications\SendSmsNotification;
use MahdiHejazi\LaravelGhasedakSms\Notifications\SimpleSmsNotification;
use MahdiHejazi\LaravelGhasedakSms\Notifications\OtpSmsNotification;
use MahdiHejazi\LaravelGhasedakSms\Channels\GhasedakChannel;

class GhasedakSmsService
{
    /**
     * Send OTP SMS using new API format with named parameters
     */
    public function sendOtp(string $phone, string $template, array $inputs = [], string $clientReferenceId = null): array
    {
        $channel = new GhasedakChannel();
        $receptors = [
            [
                'mobile' => $phone,
                'clientReferenceId' => $clientReferenceId ?? uniqid()
            ]
        ];
        $notification = new OtpSmsNotification($receptors, $template, $inputs);

        return $channel->send(null, $notification);
    }

    /**
     * Send bulk OTP SMS to multiple recipients
     */
    public function sendBulkOtp(array $phoneNumbers, string $template, array $inputs = []): array
    {
        $channel = new GhasedakChannel();
        $receptors = [];

        foreach ($phoneNumbers as $index => $phone) {
            $receptors[] = [
                'mobile' => $phone,
                'clientReferenceId' => uniqid() . '_' . $index
            ];
        }

        $notification = new OtpSmsNotification($receptors, $template, $inputs);

        return $channel->send(null, $notification);
    }

    /**
     * Send OTP verification code (using new API)
     */
    public function sendOtpVerificationCode(string $phone, string $code, string $clientReferenceId = null): array
    {
        return $this->sendOtp($phone, 'phoneVerifyCode', ['Code' => $code], $clientReferenceId);
    }

    /**
     * Send bulk OTP verification codes
     */
    public function sendBulkOtpVerificationCode(array $phoneNumbers, string $code): array
    {
        return $this->sendBulkOtp($phoneNumbers, 'phoneVerifyCode', ['Code' => $code]);
    }

    /**
     * Send scheduled OTP
     */
    public function sendScheduledOtp(string $phone, string $template, array $inputs, string $sendDate, string $clientReferenceId = null): array
    {
        $channel = new GhasedakChannel();
        $receptors = [
            [
                'mobile' => $phone,
                'clientReferenceId' => $clientReferenceId ?? uniqid()
            ]
        ];
        $notification = new OtpSmsNotification($receptors, $template, $inputs, $sendDate);

        return $channel->send(null, $notification);
    }

    /**
     * Send voice OTP
     */
    public function sendVoiceOtp(string $phone, string $template, array $inputs = [], string $clientReferenceId = null): array
    {
        $channel = new GhasedakChannel();
        $receptors = [
            [
                'mobile' => $phone,
                'clientReferenceId' => $clientReferenceId ?? uniqid()
            ]
        ];
        $notification = new OtpSmsNotification($receptors, $template, $inputs, null, true);

        return $channel->send(null, $notification);
    }

    /**
     * Send template-based SMS (legacy method)
     */
    public function sendTemplate(string $phone, string $template, array $params = []): array
    {
        $channel = new GhasedakChannel();
        $notification = new SendSmsNotification($template, $phone, $params);

        return $channel->send(null, $notification);
    }

    /**
     * Send simple SMS
     */
    public function sendSimple(string $phone, string $message, string $sender = null): array
    {
        $channel = new GhasedakChannel();
        $notification = new SimpleSmsNotification($phone, $message, $sender);

        return $channel->send(null, $notification);
    }

    /**
     * Send verification code SMS (legacy method)
     */
    public function sendVerificationCode(string $phone, string $code): array
    {
        $channel = new GhasedakChannel();
        $notification = SendSmsNotification::verificationCode($code, $phone);

        return $channel->send(null, $notification);
    }

    /**
     * Send order confirmation SMS (legacy method)
     */
    public function sendOrderConfirmed(string $phone, string $orderId, string $amount, string $date): array
    {
        $channel = new GhasedakChannel();
        $notification = SendSmsNotification::orderConfirmed($phone, $orderId, $amount, $date);

        return $channel->send(null, $notification);
    }

    /**
     * Send scheduled SMS
     */
    public function sendScheduled(string $phone, string $message, int $sendDate, string $sender = null): array
    {
        $channel = new GhasedakChannel();
        $notification = SimpleSmsNotification::scheduled($phone, $message, $sendDate, $sender);

        return $channel->send(null, $notification);
    }
}

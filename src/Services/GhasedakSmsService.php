<?php

namespace MahdiHejazi\LaravelGhasedakSms\Services;

use MahdiHejazi\LaravelGhasedakSms\Notifications\SendSmsNotification;
use MahdiHejazi\LaravelGhasedakSms\Notifications\SimpleSmsNotification;
use MahdiHejazi\LaravelGhasedakSms\Channels\GhasedakChannel;

class GhasedakSmsService
{
    /**
     * Send template-based SMS
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
     * Send verification code SMS
     */
    public function sendVerificationCode(string $phone, string $code): array
    {
        $channel = new GhasedakChannel();
        $notification = SendSmsNotification::verificationCode($code, $phone);
        
        return $channel->send(null, $notification);
    }

    /**
     * Send order confirmation SMS
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

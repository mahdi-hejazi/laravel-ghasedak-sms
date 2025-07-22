<?php

namespace MahdiHejazi\LaravelGhasedakSms\Notifications;

use MahdiHejazi\LaravelGhasedakSms\Channels\GhasedakChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendSmsNotification extends Notification
{
    use Queueable;

    private $phoneNumber;
    private $parameters;
    private $template;

    public function __construct($template, $phone, array $parameters)
    {
        $this->template = $template;
        $this->phoneNumber = $phone;
        $this->parameters = $parameters;
    }

    public function via($notifiable)
    {
        return [GhasedakChannel::class];
    }

    public function toGhasedakSms($notifiable)
    {
        $parameters = array_map(function ($value) {
            return strval($value);
        }, $this->parameters);

        return [
            'number' => $this->phoneNumber,
            'parameters' => $parameters, // Ghasedak API uses only first 3
            'template' => $this->template,
        ];
    }

    // Static factory methods for common use cases
    public static function verificationCode($code, $phone)
    {
        return new self('phoneVerifyCode', $phone, [$code]);
    }

    public static function orderConfirmed($phone, $orderId, $amount, $date)
    {
        return new self('orderConfirmed', $phone, [$orderId, $amount, $date]);
    }

    public static function thankYou($phone, $customerName)
    {
        return new self('ThankForBuy', $phone, [$customerName]);
    }

    public static function passwordReset($phone, $resetCode)
    {
        return new self('passwordReset', $phone, [$resetCode]);
    }

    public static function welcome($phone, $userName)
    {
        return new self('welcomeMessage', $phone, [$userName]);
    }
}

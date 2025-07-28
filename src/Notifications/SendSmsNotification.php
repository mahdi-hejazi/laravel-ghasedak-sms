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
            'parameters' => $parameters, // Ghasedak API supports up to 10 parameters
            'template' => $this->template,
        ];
    }

    // Static factory methods for common use cases
//    public static function verificationCode($code, $phone)
//    {
//        return new self('phoneVerifyCode', $phone, [$code]);
//    }


}

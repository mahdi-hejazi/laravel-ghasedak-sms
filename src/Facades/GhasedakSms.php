<?php

namespace MahdiHejazi\LaravelGhasedakSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendTemplate(string $phone, string $template, array $params = [])
 * @method static array sendSimple(string $phone, string $message, string $sender = null)
 * @method static array sendVerificationCode(string $phone, string $code)
 * @method static array sendOrderConfirmed(string $phone, string $orderId, string $amount, string $date)
 * @method static array sendScheduled(string $phone, string $message, int $sendDate, string $sender = null)
 * 
 * @see \MahdiHejazi\LaravelGhasedakSms\Services\GhasedakSmsService
 */
class GhasedakSms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ghasedak-sms';
    }
}

<?php

namespace MahdiHejazi\LaravelGhasedakSms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendOtp(string $phone, string $template, array $inputs = [], string $clientReferenceId = null)
 * @method static array sendTemplate(string $phone, string $template, array $params = [])
 * @method static array sendSimple(string $phone, string $message, string $sender = null)
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

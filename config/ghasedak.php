<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ghasedak SMS API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Ghasedak SMS API settings. You can get your
    | API key and sender number from your Ghasedak panel at ghasedak.me
    |
    */

    'api_key' => env('GHASEDAK_API_KEY'),

    'sender' => env('GHASEDAK_SENDER'),

    /*
    |--------------------------------------------------------------------------
    | SMS Templates
    |--------------------------------------------------------------------------
    |
    | Define your SMS templates here. The key is what you use in your code,
    | and the value is the actual template name in your Ghasedak panel.
    |
    */

    'templates' => [
        'phoneVerifyCode' => env('GHASEDAK_TEMPLATE_VERIFY_CODE', ''),
//        'orderCreated' => env('GHASEDAK_TEMPLATE_ORDER_CREATED', ''),
//        'orderConfirmed' => env('GHASEDAK_TEMPLATE_ORDER_CONFIRMED', ''),
//        'orderDeliveryInfo' => env('GHASEDAK_TEMPLATE_DELIVERY_INFO', ''),
//        'orderRequestCreated' => env('GHASEDAK_TEMPLATE_ORDER_REQUEST', ''),
//        'ThankForBuy' => env('GHASEDAK_TEMPLATE_THANK_YOU', ''),
//        'passwordReset' => env('GHASEDAK_TEMPLATE_PASSWORD_RESET', ''),
//        'welcomeMessage' => env('GHASEDAK_TEMPLATE_WELCOME', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Ghasedak API endpoints and timeouts
    |
    */

    'api' => [
        'new_otp_url' => 'https://gateway.ghasedak.me/rest/api/v1/WebService/SendOtpSMS',
        'otp_url' => 'https://gateway.ghasedak.me/rest/api/v1/WebService/SendOtpWithParams',
        'simple_url' => 'https://gateway.ghasedak.me/rest/api/v1/WebService/SendSingleSMS',
        'account_info_url' => 'https://gateway.ghasedak.me/rest/api/v1/WebService/GetAccountInformation',
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable SMS logging for debugging purposes
    |
    */

    'logging' => [
        'enabled' => env('GHASEDAK_LOGGING', false),
        'channel' => env('LOG_CHANNEL', 'stack'),
    ],
];

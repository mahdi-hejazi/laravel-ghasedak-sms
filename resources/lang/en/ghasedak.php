<?php

return [
    'errors' => [
        // Ghasedak API error codes
        '1' => 'Invalid username or password',
        '2' => 'Arrays are empty',
        '3' => 'Array length is more than 100',
        '4' => 'Sender, recipient and message text arrays do not match',
        '5' => 'Unable to get new message',
        '6' => 'Account is inactive or username/password is incorrect',
        '7' => 'No access to the desired line',
        '8' => 'Invalid recipient number',
        '9' => 'Insufficient account balance',
        '10' => 'System error occurred. Please try again',
        '11' => 'Invalid IP address',
        '20' => 'Recipient number is filtered',
        '21' => 'Connection to service provider is disconnected',

        // Custom error messages
        'invalid_template' => 'Invalid template name',
        'invalid_apikey' => 'Invalid API key',
        'insufficient_balance' => 'Insufficient balance',
        'invalid_number' => 'Invalid phone number',
        'method_not_found' => 'Required method not found in notification class',
        'template_not_found' => 'Template ":template" not found',
        'apikey_missing' => 'Ghasedak API key is not configured',
        'empty_message' => 'Message text cannot be empty',
        'empty_receptor' => 'Recipient number cannot be empty',
        'http_error' => 'HTTP request error: :status',
        'system_error' => 'System error occurred',
        'template_send_failed' => 'Template SMS sending failed',
        'simple_send_failed' => 'Simple SMS sending failed',
        'send_failed' => 'SMS sending failed',
        'unknown' => 'Unknown error: :code',
    ],
];

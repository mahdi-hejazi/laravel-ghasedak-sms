<?php

return [
    'errors' => [
        // Ghasedak API error codes
        '200' => 'Successfully completed',
        '400' => 'Invalid input parameters or recipient not entered or missing cancel message',
        '401' => 'Invalid API key or account inactive',
        '402' => 'Operation failed',
        '406' => 'Line ownership information not verified',
        '412' => 'No access to the desired line or line inactive or cannot send with public line',
        '413' => 'Message length exceeds 1000 characters or too many recipients',
        '416' => 'Source service IP does not match settings',
        '418' => 'Insufficient credit',
        '419' => 'Invalid send rate',
        '420' => 'Unauthorized link usage in message text',
        '422' => 'Message contains inappropriate characters',
        '426' => 'Using this method requires plan upgrade',
        '428' => 'Invalid message template',
        '429' => 'Parameter not found',
        '451' => 'Duplicate request',
        '500' => 'Server error occurred',

        // Custom error messages
        'invalid_template' => 'Invalid template name',
        'invalid_apikey' => 'Invalid API key',
        'insufficient_balance' => 'Insufficient balance',
        'invalid_number' => 'Invalid phone number',
        'method_not_found' => 'Required method not found in notification class',
        'template_not_found_in_config' => 'Template ":template" not found. please add it to config file.',
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

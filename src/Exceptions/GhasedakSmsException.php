<?php

namespace MahdiHejazi\LaravelGhasedakSms\Exceptions;

use Exception;

class GhasedakSmsException extends Exception
{
    protected $errorCode;
    protected $originalMessage;

    public function __construct($errorCode, $originalMessage = null, $code = 0, Exception $previous = null)
    {
        $this->errorCode = $errorCode;
        $this->originalMessage = $originalMessage;

        // Use provided message or get Persian error message
        $persianMessage = $originalMessage ?? $this->getPersianErrorMessage($errorCode);

        parent::__construct($persianMessage, $code, $previous);
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getOriginalMessage()
    {
        return $this->originalMessage;
    }

    private function getPersianErrorMessage($errorCode): string
    {
        $errorMap = [
            // Ghasedak API error codes
            200 => __('ghasedak::errors.200'),
            400 => __('ghasedak::errors.400'),
            401 => __('ghasedak::errors.401'),
            402 => __('ghasedak::errors.402'),
            406 => __('ghasedak::errors.406'),
            412 => __('ghasedak::errors.412'),
            413 => __('ghasedak::errors.413'),
            416 => __('ghasedak::errors.416'),
            418 => __('ghasedak::errors.418'),
            419 => __('ghasedak::errors.419'),
            420 => __('ghasedak::errors.420'),
            422 => __('ghasedak::errors.422'),
            426 => __('ghasedak::errors.426'),
            428 => __('ghasedak::errors.428'),
            429 => __('ghasedak::errors.429'),
            451 => __('ghasedak::errors.451'),
            500 => __('ghasedak::errors.500'),
            
            // Custom error codes
            'invalid template' => __('ghasedak::errors.invalid_template'),
            'apikey is invalid' => __('ghasedak::errors.invalid_apikey'),
            'insufficient balance' => __('ghasedak::errors.insufficient_balance'),
            'invalid number' => __('ghasedak::errors.invalid_number'),
            'method_not_found' => __('ghasedak::errors.method_not_found'),
            'template_not_found' => __('ghasedak::errors.template_not_found'),
            'apikey_missing' => __('ghasedak::errors.apikey_missing'),
            'empty_message' => __('ghasedak::errors.empty_message'),
            'empty_receptor' => __('ghasedak::errors.empty_receptor'),
            'http_error' => __('ghasedak::errors.http_error'),
            'system_error' => __('ghasedak::errors.system_error'),
            'template_send_failed' => __('ghasedak::errors.template_send_failed'),
            'simple_send_failed' => __('ghasedak::errors.simple_send_failed'),
            'send_failed' => __('ghasedak::errors.send_failed'),
        ];

        return $errorMap[$errorCode] ?? __('ghasedak::errors.unknown', ['code' => $errorCode]);
    }

    /**
     * Static method to throw exception with error code
     */
    public static function throwError($errorCode, $originalMessage = null)
    {
        throw new self($errorCode, $originalMessage);
    }
}

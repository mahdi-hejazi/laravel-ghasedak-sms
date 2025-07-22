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
            1 => __('ghasedak::errors.1'),
            2 => __('ghasedak::errors.2'),
            3 => __('ghasedak::errors.3'),
            4 => __('ghasedak::errors.4'),
            5 => __('ghasedak::errors.5'),
            6 => __('ghasedak::errors.6'),
            7 => __('ghasedak::errors.7'),
            8 => __('ghasedak::errors.8'),
            9 => __('ghasedak::errors.9'),
            10 => __('ghasedak::errors.10'),
            11 => __('ghasedak::errors.11'),
            20 => __('ghasedak::errors.20'),
            21 => __('ghasedak::errors.21'),
            
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

<?php

namespace MahdiHejazi\LaravelGhasedakSms\Helpers;

class PhoneHelper
{
    /**
     * Clean and normalize Iranian phone number
     */
    public static function clean($phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Remove country code if present
        if (str_starts_with($phone, '98')) {
            $phone = substr($phone, 2);
        }

        // Add leading zero if missing
        if (!str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        // Validate Iranian mobile format (09xxxxxxxxx)
        if (!preg_match('/^09\d{9}$/', $phone)) {
            throw new \InvalidArgumentException("Invalid Iranian mobile number: {$phone}");
        }

        return $phone;
    }

    /**
     * Validate Iranian mobile number
     */
    public static function isValid($phone): bool
    {
        try {
            self::clean($phone);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
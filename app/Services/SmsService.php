<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * SMS service layer ready for M-Pesa/Airtel gateway or third-party SMS API integration.
 */
class SmsService
{
    public function send(string $phone, string $message): bool
    {
        $driver = config('services.sms.driver', env('SMS_DRIVER', 'log'));

        return match ($driver) {
            'log' => $this->logDriver($phone, $message),
            default => $this->logDriver($phone, $message),
        };
    }

    protected function logDriver(string $phone, string $message): bool
    {
        Log::channel('single')->info('SMS sent', ['phone' => $phone, 'message' => $message]);

        return true;
    }
}

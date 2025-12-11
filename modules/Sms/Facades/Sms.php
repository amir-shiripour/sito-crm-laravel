<?php

namespace Modules\Sms\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Sms\Services\SmsManager;

/**
 * @method static \Modules\Sms\Entities\SmsMessage send(string $to, string $message, array $options = [])
 * @method static \Modules\Sms\Entities\SmsMessage sendPattern(string $to, string $patternKey, array $data = [], array $options = [])
 * @method static int|null getBalance()
 * @method static array getDrivers()
 */
class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SmsManager::class;
    }
}

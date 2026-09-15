<?php

namespace Modules\Notifications\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Notifications\Services\NotificationService;

/**
 * @method static int send(mixed $recipients, string $title, string $message, string $category = 'system', string $priority = 'normal', string $severity = 'info', ?string $actionUrl = null, array $channels = [], array $options = [])
 *
 * @see \Modules\Notifications\Services\NotificationService
 */
class NotificationCenter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NotificationService::class;
    }
}

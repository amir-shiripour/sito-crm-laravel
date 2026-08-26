<?php

namespace App\Services\Cron;

use App\Models\CronTask;
use App\Models\CronTaskLog;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;
use Symfony\Component\Console\Output\BufferedOutput;

class CronService
{
    const HEARTBEAT_CACHE_KEY = 'cron_last_server_pulse';

    /**
     * Record a heartbeat ping when schedule:run executes.
     */
    public static function recordHeartbeat(): void
    {
        try {
            Cache::forever(self::HEARTBEAT_CACHE_KEY, now()->timestamp);
        } catch (\Throwable $e) {
            // Fail silently if cache driver is temporarily down
        }
    }

    /**
     * Get server cron health and status info.
     */
    public static function getHeartbeatInfo(): array
    {
        $timestamp = Cache::get(self::HEARTBEAT_CACHE_KEY);
        $lastPulse = $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp) : null;
        
        $isActive = false;
        $relativeTime = 'تاکنون تماسی ثبت نشده است';
        $jalaliTime = '-';

        if ($lastPulse) {
            $diffSeconds = now()->diffInSeconds($lastPulse);
            // If cron ran within the last 10 minutes, consider it healthy/active
            $isActive = $diffSeconds <= 600;

            if ($diffSeconds < 60) {
                $relativeTime = $diffSeconds . ' ثانیه پیش';
            } elseif ($diffSeconds < 3600) {
                $relativeTime = round($diffSeconds / 60) . ' دقیقه پیش';
            } elseif ($diffSeconds < 86400) {
                $relativeTime = round($diffSeconds / 3600) . ' ساعت پیش';
            } else {
                $relativeTime = round($diffSeconds / 86400) . ' روز پیش';
            }

            try {
                $jalaliTime = Jalalian::fromCarbon($lastPulse)->format('Y/m/d H:i:s');
            } catch (\Throwable $e) {
                $jalaliTime = $lastPulse->toDateTimeString();
            }
        }

        $basePath = base_path();
        $phpPath = PHP_BINARY ?: 'php';
        $recommendedCron = "* * * * * cd {$basePath} && {$phpPath} artisan schedule:run >> /dev/null 2>&1";

        return [
            'is_active' => $isActive,
            'last_pulse' => $lastPulse,
            'relative_time' => $relativeTime,
            'jalali_time' => $jalaliTime,
            'recommended_cron' => $recommendedCron,
        ];
    }

    /**
     * Check critical storage and cache directory permissions for Linux/www-data.
     */
    public static function checkPermissions(): array
    {
        $basePath = base_path();
        $paths = [
            'storage' => storage_path(),
            'storage/logs' => storage_path('logs'),
            'storage/framework' => storage_path('framework'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $results = [];
        $allHealthy = true;

        foreach ($paths as $name => $path) {
            $exists = file_exists($path);
            $isWritable = $exists && is_writable($path);
            $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
            
            if (!$exists || !$isWritable) {
                $allHealthy = false;
            }

            $results[$name] = [
                'name' => $name,
                'path' => $path,
                'exists' => $exists,
                'is_writable' => $isWritable,
                'permissions' => $perms,
            ];
        }

        $currentUser = 'unknown';
        if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $userInfo = posix_getpwuid(posix_geteuid());
            $currentUser = $userInfo['name'] ?? 'unknown';
        } elseif (function_exists('get_current_user')) {
            $currentUser = get_current_user();
        }

        $fixCommands = "sudo chown -R www-data:www-data {$basePath}\nsudo chmod -R 775 {$basePath}/storage {$basePath}/bootstrap/cache";

        return [
            'all_healthy' => $allHealthy,
            'current_user' => $currentUser,
            'paths' => $results,
            'fix_commands' => $fixCommands,
        ];
    }

    /**
     * Seed known standard module tasks if not already present.
     */
    public static function seedDefaultTasks(): void
    {
        if (!Schema::hasTable('cron_tasks')) {
            return;
        }

        $defaults = [
            [
                'module' => 'Workflows',
                'command' => 'workflows:process',
                'name' => 'پردازش گام‌های گردش‌کار (Workflows)',
                'description' => 'بررسی شرایط و اجرای اکشن‌ها، تریگرها و مراحل فعال در پایپ‌لاین‌های گردش کار',
                'expression' => 'everyMinute',
                'prevent_overlap' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'module' => 'Booking',
                'command' => 'booking:handle-payment-timeouts',
                'name' => 'مدیریت تایم‌اوت‌های پرداخت نوبت‌دهی',
                'description' => 'بررسی نوبت‌های معلق مانده در درگاه پرداخت و لغو/آزادسازی اسلات‌های منقضی شده',
                'expression' => 'everyMinute',
                'prevent_overlap' => true,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'module' => 'Booking',
                'command' => 'booking:dispatch-reminders',
                'name' => 'ارسال پیامک یادآوری نوبت‌های پیش‌رو',
                'description' => 'ارسال خودکار پیامک یادآور به مشتریان قبل از موعد نوبت‌دهی طبق بازه تنظیمی',
                'expression' => 'everyFiveMinutes',
                'prevent_overlap' => true,
                'is_active' => false,
                'is_system' => true,
            ],
            [
                'module' => 'Booking',
                'command' => 'booking:cleanup-holds',
                'name' => 'پاکسازی رزروهای موقت (Cleanup Holds)',
                'description' => 'آزادسازی زمان‌های انتخاب شده توسط کاربران که فرایند رزرو را نهایی نکرده‌اند',
                'expression' => 'everyTenMinutes',
                'prevent_overlap' => true,
                'is_active' => false,
                'is_system' => true,
            ],
            [
                'module' => 'Sms',
                'command' => 'sms:process-scheduled',
                'name' => 'پردازش صف پیامک‌های زمان‌بندی شده',
                'description' => 'بررسی پیامک‌های دارای تاریخ ارسال آتی و مخابره آن‌ها به وب‌سرویس پیامک',
                'expression' => 'everyMinute',
                'prevent_overlap' => true,
                'is_active' => false,
                'is_system' => true,
            ],
            [
                'module' => 'Reminders',
                'command' => 'reminders:process',
                'name' => 'پردازش یادآورهای کاربران و پیگیری‌ها',
                'description' => 'ارسال اعلان‌های سیستمی و نوتیفیکیشن‌های یادآوری وظایف به پرسنل و کاربران',
                'expression' => 'everyFiveMinutes',
                'prevent_overlap' => true,
                'is_active' => false,
                'is_system' => true,
            ],
        ];

        foreach ($defaults as $taskData) {
            CronTask::firstOrCreate(
                ['command' => $taskData['command']],
                [
                    'module' => $taskData['module'],
                    'name' => $taskData['name'],
                    'description' => $taskData['description'],
                    'expression' => $taskData['expression'],
                    'is_active' => $taskData['is_active'],
                    'prevent_overlap' => $taskData['prevent_overlap'],
                    'is_system' => $taskData['is_system'],
                    'next_run_at' => self::calculateNextRun($taskData['expression']),
                ]
            );
        }
    }

    /**
     * Run a task immediately and record complete output & duration.
     */
    public static function runTask(CronTask $task, string $triggeredBy = 'system_cron'): array
    {
        $startTime = microtime(true);
        $startedAt = now();

        $log = CronTaskLog::create([
            'cron_task_id' => $task->id,
            'status' => 'running',
            'started_at' => $startedAt,
            'triggered_by' => $triggeredBy,
        ]);

        $task->update([
            'last_status' => 'running',
        ]);

        $outputBuffer = new BufferedOutput();
        $exitCode = 0;
        $status = 'success';
        $errorMessage = null;

        try {
            // Split command and parameters if any
            $commandParts = explode(' ', trim($task->command));
            $artisanCommand = array_shift($commandParts);
            $params = [];

            // Add arguments if structured
            if (!empty($task->parameters) && is_array($task->parameters)) {
                $params = $task->parameters;
            }

            $exitCode = Artisan::call($task->command, $params, $outputBuffer);
            $output = $outputBuffer->fetch();

            if ($exitCode !== 0) {
                $status = 'failed';
                $errorMessage = "Command exited with non-zero code: {$exitCode}";
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            $output = ($outputBuffer->fetch() ?: '') . "\n\nException: " . $e->getMessage() . "\n" . $e->getTraceAsString();
            Log::error("Cron Task Execution Failed [{$task->command}]: " . $e->getMessage());
        }

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);
        $finishedAt = now();

        // Update Log
        $log->update([
            'status' => $status,
            'finished_at' => $finishedAt,
            'duration_ms' => $durationMs,
            'output' => $output ?: ($status === 'success' ? 'دستور با موفقیت و بدون خروجی متنی اجرا شد.' : 'اجرا با خطا مواجه شد.'),
            'error_message' => $errorMessage,
        ]);

        // Calculate next run
        $nextRun = self::calculateNextRun($task->expression);

        // Update Task Record
        $task->update([
            'last_run_at' => $startedAt,
            'next_run_at' => $nextRun,
            'last_status' => $status,
            'last_duration_ms' => $durationMs,
            'last_error_message' => $errorMessage,
        ]);

        return [
            'success' => $status === 'success',
            'status' => $status,
            'duration_ms' => $durationMs,
            'output' => $output ?: 'دستور بدون خروجی کنسولی پایان یافت.',
            'error' => $errorMessage,
            'finished_at' => $finishedAt,
        ];
    }

    /**
     * Apply frequency expression to Laravel scheduler event.
     */
    public static function applyFrequency(Event $event, string $expression): Event
    {
        return match ($expression) {
            'everyMinute' => $event->everyMinute(),
            'everyTwoMinutes' => $event->everyTwoMinutes(),
            'everyThreeMinutes' => $event->everyThreeMinutes(),
            'everyFiveMinutes' => $event->everyFiveMinutes(),
            'everyTenMinutes' => $event->everyTenMinutes(),
            'everyFifteenMinutes' => $event->everyFifteenMinutes(),
            'everyThirtyMinutes' => $event->everyThirtyMinutes(),
            'hourly' => $event->hourly(),
            'everyTwoHours' => $event->everyTwoHours(),
            'everyThreeHours' => $event->everyThreeHours(),
            'everySixHours' => $event->everySixHours(),
            'daily' => $event->daily(),
            'weekly' => $event->weekly(),
            'monthly' => $event->monthly(),
            default => $event->cron(self::sanitizeCronExpression($expression)),
        };
    }

    /**
     * Estimate next run datetime for a given frequency expression.
     */
    public static function calculateNextRun(string $expression): ?\Carbon\Carbon
    {
        try {
            $cronExpr = self::toStandardCronString($expression);
            $cron = new CronExpression($cronExpr);
            return \Carbon\Carbon::instance($cron->getNextRunDate());
        } catch (\Throwable $e) {
            return now()->addMinutes(5);
        }
    }

    /**
     * Translate friendly frequency words to standard 5-part cron syntax.
     */
    public static function toStandardCronString(string $expression): string
    {
        return match ($expression) {
            'everyMinute' => '* * * * *',
            'everyTwoMinutes' => '*/2 * * * *',
            'everyThreeMinutes' => '*/3 * * * *',
            'everyFiveMinutes' => '*/5 * * * *',
            'everyTenMinutes' => '*/10 * * * *',
            'everyFifteenMinutes' => '*/15 * * * *',
            'everyThirtyMinutes' => '*/30 * * * *',
            'hourly' => '0 * * * *',
            'everyTwoHours' => '0 */2 * * *',
            'everyThreeHours' => '0 */3 * * *',
            'everySixHours' => '0 */6 * * *',
            'daily' => '0 0 * * *',
            'weekly' => '0 0 * * 0',
            'monthly' => '0 0 1 * *',
            default => self::sanitizeCronExpression($expression),
        };
    }

    /**
     * Ensure custom cron expressions have 5 parts.
     */
    public static function sanitizeCronExpression(string $expression): string
    {
        $expression = trim($expression);
        $parts = explode(' ', preg_replace('/\s+/', ' ', $expression));
        if (count($parts) === 5) {
            return $expression;
        }
        return '*/5 * * * *'; // Safe default
    }

    /**
     * Register dynamic database tasks into Laravel Schedule instance.
     */
    public static function registerDynamicSchedule(Schedule $schedule): void
    {
        // 1. Always record heartbeat
        $schedule->call([self::class, 'recordHeartbeat'])->everyMinute()->name('cron-heartbeat-pulse');

        // 2. Safely query active tasks from database
        try {
            if (!Schema::hasTable('cron_tasks')) {
                return;
            }

            $tasks = CronTask::query()->where('is_active', true)->get();

            foreach ($tasks as $task) {
                // Check if parent module is active
                if (!$task->isModuleActive()) {
                    continue;
                }

                // Create schedule command event
                $event = $schedule->command($task->command);

                // Apply frequency
                self::applyFrequency($event, $task->expression);

                // Prevent overlap if enabled
                if ($task->prevent_overlap) {
                    $event->withoutOverlapping(60); // 60 minutes mutex
                }

                // Run in background if enabled
                if ($task->run_in_background) {
                    $event->runInBackground();
                }

                // Hook before execution: mark running
                $event->before(function () use ($task) {
                    $task->update(['last_status' => 'running']);
                });

                // Hook after execution: run with full logging
                $event->after(function () use ($task) {
                    self::runTask($task, 'system_cron');
                });
            }
        } catch (\Throwable $e) {
            Log::warning("Dynamic Cron Registration Warning: " . $e->getMessage());
        }
    }
}

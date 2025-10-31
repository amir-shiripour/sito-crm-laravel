<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
//use Illuminate\Support\Facades\Artisan;
//use Illuminate\Support\Facades\DB;
/**
 * سرویسی برای نوشتن مقادیر جدید در فایل .env
 * * این کلاس فایل .env را می‌خواند، مقادیر مورد نظر را جایگزین یا اضافه می‌کند
 * و سپس فایل را بازنویسی می‌کند.
 */
class EnvWriter
{
    /**
     * مسیر فایل .env
     * @var string
     */
    protected $envPath;

    public function __construct()
    {
        $this->envPath = app()->environmentFilePath();
    }

    /**
     * یک مقدار را در فایل .env بازنویسی یا اضافه می‌کند.
     *
     * @param string $key کلید (مانند DB_HOST)
     * @param string $value مقدار (مانند 127.0.0.1)
     * @return bool
     * @throws \Exception
     */
    public function overwrite(string $key, string $value): bool
    {
        if (!File::exists($this->envPath)) {
            throw new \Exception("فایل .env پیدا نشد.");
        }

        $content = File::get($this->envPath);
        $key = strtoupper($key); // اطمینان از اینکه کلید با حروف بزرگ است

        // بررسی اینکه آیا کلید از قبل وجود دارد یا نه
        // الگوی Regex برای پیدا کردن KEY=VALUE
        // این الگو مقادیر داخل کوتیشن ("VALUE") را هم در نظر می‌گیرد
        $pattern = "/^{$key}=.*$/m";

        $newLine = "{$key}={$value}";

        if (preg_match($pattern, $content)) {
            // اگر کلید وجود داشت، آن را جایگزین کن
            $content = preg_replace($pattern, $newLine, $content);
        } else {
            // اگر کلید وجود نداشت، آن را به انتهای فایل اضافه کن
            // اطمینان از وجود یک خط جدید در انتهای فایل قبل از افزودن
            $content = rtrim($content, "\n\r") . "\n" . $newLine;
        }

        // نوشتن محتوای جدید در فایل .env
        try {
            File::put($this->envPath, $content);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("خطا در هنگام نوشتن در فایل .env: " . $e->getMessage());
        }
    }
}


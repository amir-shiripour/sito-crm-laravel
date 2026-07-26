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
        $key = strtoupper(trim($key));

        // یکنواخت‌سازی کاراکترهای پایان خط
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);
        $keyFound = false;

        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$currentKey] = explode('=', $line, 2);
                if (strtoupper(trim($currentKey)) === $key) {
                    $lines[$index] = "{$key}={$value}";
                    $keyFound = true;
                    break;
                }
            }
        }

        if (!$keyFound) {
            $lines[] = "{$key}={$value}";
        }

        $newContent = implode("\n", $lines) . "\n";

        try {
            File::put($this->envPath, $newContent);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("خطا در هنگام نوشتن در فایل .env: " . $e->getMessage());
        }
    }
}


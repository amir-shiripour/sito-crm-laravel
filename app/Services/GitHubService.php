<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    protected $repo = 'amir-shiripour/sito-crm-laravel';
    protected $branch = 'main';

    /**
     * دریافت اطلاعات آخرین کامیت از برنچ اصلی
     */
    public function getLatestRemoteInfo()
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Sito-CRM-Agent'
            ])->get("https://api.github.com/repos/{$this->repo}/branches/{$this->branch}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("GitHub API Error: " . $e->getMessage());
            return null;
        }
        return null;
    }

    /**
     * اجرای عملیات به‌روزرسانی خودکار با ثبت لاگ دقیق
     */
    public function performUpdate()
    {
        try {
            Log::info("Starting Smart Deployment from GitHub...");

            // 1. حالت تعمیرات
            Process::run('php artisan down');

            // 2. اجرای Git Pull
            $pull = Process::run('git pull origin ' . $this->branch);

            if (!$pull->successful()) {
                Log::error("Git Pull Failed: " . $pull->errorOutput());
                Process::run('php artisan up');
                return [
                    'success' => false,
                    'message' => 'خطا در واکشی کدها از گیت‌هاب. خروجی: ' . $pull->errorOutput()
                ];
            }

            Log::info("Git Pull Successful. Output: " . $pull->output());

            // 3. آپدیت وابستگی‌ها و دیتابیس
            Process::run('composer install --no-dev --optimize-autoloader');
            Process::run('php artisan migrate --force');
            Process::run('php artisan optimize:clear');
            Process::run('php artisan up');

            Log::info("Deployment completed successfully.");

            return [
                'success' => true,
                'message' => 'سیستم با موفقیت به آخرین نسخه گیت‌هاب به‌روزرسانی شد.'
            ];

        } catch (\Exception $e) {
            Log::error("Critical Deployment Error: " . $e->getMessage());
            Process::run('php artisan up');
            return [
                'success' => false,
                'message' => 'خطای سیستمی در فرآیند استقرار: ' . $e->getMessage()
            ];
        }
    }
}

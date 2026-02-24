<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

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
            return null;
        }
        return null;
    }

    /**
     * اجرای عملیات به‌روزرسانی خودکار
     * هشدار: این متد نیاز به دسترسی Git در سرور دارد
     */
    public function performUpdate()
    {
        // 1. قرار دادن سیستم در حالت تعمیرات
        Process::run('php artisan down');

        // 2. دریافت آخرین کدها
        $pull = Process::run('git pull origin ' . $this->branch);

        if ($pull->successful()) {
            // 3. اجرای دستورات پاکسازی و مایگریشن
            Process::run('composer install --no-dev --optimize-autoloader');
            Process::run('php artisan migrate --force');
            Process::run('php artisan optimize:clear');
            Process::run('php artisan up');

            return [
                'success' => true,
                'message' => 'سیستم با موفقیت به آخرین نسخه گیت‌هاب به‌روزرسانی شد.'
            ];
        }

        Process::run('php artisan up');
        return [
            'success' => false,
            'message' => 'خطا در اجرای دستور Git: ' . $pull->errorOutput()
        ];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // پاک کردن جدول قبل از سید کردن
//        Module::truncate();

        // ماژول هسته (مجازی)
        // این ماژول فیزیکی نیست اما برای نمایش در دیتابیس و مدیریت‌های آتی استفاده می‌شود
        Module::create([
            'name' => 'ماژول هسته',
            'slug' => 'core',
            'description' => 'شامل تنظیمات اصلی، داشبورد و قابلیت‌های پایه سیستم.',
            'version' => '1.0.0',
            'active' => true,
            'is_core' => true, // این ماژول مجازی و هسته‌ای است
        ]);

        // ماژول UserManagement از اینجا حذف شد چون به هسته (app) منتقل شده است.
        // ماژول‌های Blog و Shop طبق درخواست حذف شدند.
        // در آینده، ماژول‌های اختیاری و فیزیکی جدید (مثل nwidart)
        // می‌توانند با is_core = false در اینجا اضافه شوند.
    }
}


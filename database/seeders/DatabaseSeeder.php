<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // !مهم: ترتیب اجرای سیدرها اهمیت دارد
        // ابتدا باید ماژول‌ها ثبت شوند
        $this->call(ModuleSeeder::class);

        // سپس تم‌ها و نیازمندی‌های ماژولی آن‌ها ثبت شوند
        $this->call(ThemeSeeder::class);
    }
}


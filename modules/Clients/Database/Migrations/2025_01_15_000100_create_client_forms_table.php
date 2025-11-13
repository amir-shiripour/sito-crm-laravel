<?php
// Modules/Clients/Database/Migrations/2025_01_15_000100_create_client_forms_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // مثلا "فرم پیش‌فرض" | "فرم کامل"
            $table->string('key')->unique();  // اسلاگ فرم
            $table->boolean('is_default')->default(false);
            $table->json('schema');           // تعریف فیلدها و تنظیمات
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('client_forms');
    }
};

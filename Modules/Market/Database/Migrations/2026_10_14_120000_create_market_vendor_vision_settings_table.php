<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_vendor_vision_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained('market_vendors')->cascadeOnDelete();
            $table->boolean('is_active')->default(false);
            $table->string('server_url')->nullable();
            $table->string('ws_username')->nullable();
            $table->text('ws_password')->nullable();
            $table->text('admin_pass')->nullable(); // رمز ادمین تنظیمات شرکت PDA نسخه 3.6.1
            $table->string('default_visitor_code')->nullable();
            $table->string('default_warehouse_code')->nullable();
            $table->string('default_customer_code')->nullable(); // کد مشتری اینترنتی/عمومی
            $table->boolean('auto_sync_stock')->default(false);
            $table->boolean('auto_dispatch_order')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_sync_status')->nullable(); // success, failed
            $table->text('last_error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_vendor_vision_settings');
    }
};

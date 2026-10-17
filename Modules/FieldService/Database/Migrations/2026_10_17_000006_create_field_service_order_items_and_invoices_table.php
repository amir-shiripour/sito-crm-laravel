<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_invoices')) {
            Schema::create('field_service_invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('field_service_orders')->cascadeOnDelete();
                $table->string('invoice_number', 50)->unique();
                
                // Financial breakdown
                $table->decimal('labor_amount', 14, 2)->default(0);    // اجرت تعمیرات و خدمات
                $table->decimal('parts_amount', 14, 2)->default(0);    // هزینه قطعات تعویضی
                $table->decimal('travel_fee', 14, 2)->default(0);      // هزینه ثابت ایاب و ذهاب
                $table->decimal('discount_amount', 14, 2)->default(0);
                $table->decimal('total_amount', 14, 2)->default(0);    // مجموع کل پرداختی مشتری
                
                // Commission sharing (15% platform / 85% technician)
                $table->decimal('platform_commission_percent', 5, 2)->default(15.00);
                $table->decimal('platform_commission_amount', 14, 2)->default(0);
                $table->decimal('technician_net_amount', 14, 2)->default(0);
                
                // Escrow & Payment lifecycle
                $table->string('payment_method', 30)->default('online'); // online, wallet, cash, pos
                $table->string('payment_status', 30)->default('pending'); // PaymentStatus enum: pending, escrow_held, released, refunded
                $table->string('tracking_number', 100)->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('escrow_released_at')->nullable();
                $table->date('warranty_until')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('field_service_order_items')) {
            Schema::create('field_service_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('field_service_invoices')->cascadeOnDelete();
                $table->string('item_type', 30)->default('labor'); // labor, part, travel, other
                $table->string('title', 150); // e.g. تعویض مبدل ثانویه / شارژ گاز R410
                $table->decimal('unit_price', 14, 2)->default(0);
                $table->unsignedSmallInteger('quantity')->default(1);
                $table->decimal('total_price', 14, 2)->default(0);
                $table->unsignedTinyInteger('warranty_months')->default(3);
                $table->string('part_serial_number', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_order_items');
        Schema::dropIfExists('field_service_invoices');
    }
};

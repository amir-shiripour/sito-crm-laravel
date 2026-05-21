<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // جدول اقلام یا ردیف های هر صورت حساب
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // اتصال به جدول اصلی صورت حساب ها
            // با حذف یک فاکتور، تمام اقلام آن نیز حذف می شوند
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');

            $table->string('description')->comment('شرح کالا، خدمات یا عنوان ردیف');
            $table->decimal('quantity', 8, 2)->default(1)->comment('تعداد یا مقدار');
            $table->decimal('unit_price', 15, 2)->comment('قیمت واحد هر قلم');
            $table->decimal('total_price', 15, 2)->comment('قیمت کل این ردیف (تعداد * قیمت واحد)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
};

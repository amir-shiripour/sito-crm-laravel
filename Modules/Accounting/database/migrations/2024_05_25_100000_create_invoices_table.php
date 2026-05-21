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
        // جدول اصلی برای نگهداری صورت حساب ها
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // فرض بر این است که جدولی برای مشتریان با نام clients وجود دارد
            $table->foreignId('client_id')->constrained('clients')->comment('مشتری مرتبط با این فاکتور');

            $table->string('invoice_number')->unique()->comment('شماره منحصر به فرد فاکتور');

            $table->date('issue_date')->comment('تاریخ صدور فاکتور');
            $table->date('due_date')->nullable()->comment('تاریخ سررسید پرداخت');

            $table->decimal('subtotal', 15, 2)->comment('جمع مبلغ اقلام قبل از تخفیف و مالیات');
            $table->decimal('discount', 15, 2)->default(0)->comment('مبلغ تخفیف روی کل فاکتور');
            $table->decimal('tax', 15, 2)->default(0)->comment('مبلغ مالیات بر ارزش افزوده');
            $table->decimal('total_amount', 15, 2)->comment('مبلغ نهایی قابل پرداخت');

            $table->enum('status', ['draft', 'unpaid', 'paid', 'cancelled'])
                  ->default('unpaid')
                  ->comment('وضعیت فاکتور: پیش نویس، پرداخت نشده، پرداخت شده، باطل شده');

            $table->text('notes')->nullable()->comment('یادداشت های مربوط به فاکتور');

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
        Schema::dropIfExists('invoices');
    }
};

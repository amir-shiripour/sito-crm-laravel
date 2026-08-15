<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense'])->comment('ماهیت: دارایی، بدهی، سرمایه، درآمد، هزینه');
            $table->boolean('status')->default(true)->comment('فعال/غیر فعال');
            $table->boolean('is_system')->default(false)->comment('سرفصل سیستمی (غیرقابل حذف)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_categories');
    }
};

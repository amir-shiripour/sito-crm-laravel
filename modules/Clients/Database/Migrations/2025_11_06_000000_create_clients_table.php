<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();  // کاربر ایجادکننده
            $table->timestamps();
            $table->softDeletes();  // اگر بخواهی حذف نرم (اختیاری)
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}

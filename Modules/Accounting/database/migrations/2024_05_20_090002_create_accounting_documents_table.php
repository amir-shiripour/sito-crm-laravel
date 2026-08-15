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
        Schema::create('accounting_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->date('document_date');
            $table->text('description')->nullable();
            $table->nullableMorphs('documentable'); // For linking to other modules (appointments, shop, etc.)
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
        Schema::dropIfExists('accounting_documents');
    }
};

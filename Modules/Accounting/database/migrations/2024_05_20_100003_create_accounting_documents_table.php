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
            $table->foreignId('bank_id')->constrained('accounting_banks');
            $table->foreignId('category_id')->constrained('accounting_categories');
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 15, 2);
            $table->date('document_date');
            $table->text('description')->nullable();
            $table->nullableMorphs('documentable');
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
        Schema::dropIfExists('accounting_documents');
    }
};

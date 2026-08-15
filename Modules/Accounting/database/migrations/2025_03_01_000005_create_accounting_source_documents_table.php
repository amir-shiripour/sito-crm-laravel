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
        if (!Schema::hasTable('accounting_source_documents')) {
            Schema::create('accounting_source_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('accounting_documents')->cascadeOnDelete();
                $table->string('sourceable_type');
                $table->unsignedBigInteger('sourceable_id');
                $table->string('module');     // services, booking, market
                $table->string('event_type'); // invoice_paid, payment_confirmed, order_paid
                $table->json('snapshot')->nullable();
                $table->timestamps();

                $table->index(['sourceable_type', 'sourceable_id'], 'src_doc_morph_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_source_documents');
    }
};

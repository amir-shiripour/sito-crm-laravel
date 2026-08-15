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
        if (!Schema::hasTable('accounting_cheque_attachments')) {
            Schema::create('accounting_cheque_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cheque_id')->constrained('accounting_cheques')->cascadeOnDelete();
                $table->morphs('attachable', 'cheque_att_morph_idx'); // invoice or document
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_cheque_attachments');
    }
};

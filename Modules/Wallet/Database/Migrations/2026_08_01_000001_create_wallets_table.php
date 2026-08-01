<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallets')) {
            return;
        }

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->morphs('holder'); // holder_type, holder_id
            $table->string('slug')->default('default'); // e.g., 'default', 'gift', 'system_credit'
            $table->string('name')->nullable();
            $table->decimal('balance', 18, 4)->default(0);
            $table->string('currency', 10)->default('IRR');
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['holder_type', 'holder_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};

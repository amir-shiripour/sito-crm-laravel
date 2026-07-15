<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_vendor_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('market_vendors')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['vendor_id', 'user_id']);
        });

        // Copy existing owners to the pivot table
        $vendors = DB::table('market_vendors')->select('id', 'user_id', 'created_at', 'updated_at')->get();
        foreach ($vendors as $vendor) {
            DB::table('market_vendor_user')->insertOrIgnore([
                'vendor_id' => $vendor->id,
                'user_id' => $vendor->user_id,
                'created_at' => $vendor->created_at ?: now(),
                'updated_at' => $vendor->updated_at ?: now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_vendor_user');
    }
};

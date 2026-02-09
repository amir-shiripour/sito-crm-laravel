<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('booking_settings')) {
            return;
        }

        Schema::create('booking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('currency_unit', 10)->default('IRR');
            $table->boolean('global_online_booking_enabled')->default(true);
            $table->unsignedInteger('default_slot_duration_minutes')->default(30);
            $table->unsignedInteger('default_capacity_per_slot')->default(1);
            $table->unsignedInteger('default_capacity_per_day')->nullable();

            $table->boolean('allow_role_service_creation')->default(false);
            $table->json('allowed_roles')->nullable();

            // Optional scopes for ownership rules
            $table->string('category_management_scope', 10)->default('ALL'); // ALL/OWN
            $table->string('form_management_scope', 10)->default('ALL'); // ALL/OWN
            $table->string('service_category_selection_scope', 10)->default('ALL'); // ALL/OWN
            $table->string('service_form_selection_scope', 10)->default('ALL'); // ALL/OWN

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_settings');
    }
};

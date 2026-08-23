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
        if (!Schema::hasTable('menu_custom_groups')) {
            Schema::create('menu_custom_groups', function (Blueprint $table) {
                $table->id();
                $table->string('group_key')->unique();
                $table->string('title');
                $table->text('icon')->nullable();
                $table->integer('position')->default(99);
                $table->string('scope')->default('global'); // global, role, user
                $table->unsignedBigInteger('scope_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['scope', 'scope_id']);
            });
        }

        if (!Schema::hasTable('menu_customizations')) {
            Schema::create('menu_customizations', function (Blueprint $table) {
                $table->id();
                $table->string('scope')->default('global'); // global, role, user
                $table->unsignedBigInteger('scope_id')->nullable();
                $table->string('menu_key'); // module|route or group_key
                $table->string('type')->default('item'); // item, group
                $table->json('overrides'); // title, icon, position, group_key, hidden, visibility_type, allowed_roles, allowed_users
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique(['scope', 'scope_id', 'menu_key'], 'menu_cust_scope_key_unique');
                $table->index(['scope', 'scope_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_customizations');
        Schema::dropIfExists('menu_custom_groups');
    }
};

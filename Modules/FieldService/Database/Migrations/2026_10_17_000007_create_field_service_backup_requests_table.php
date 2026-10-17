<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('field_service_backup_requests')) {
            Schema::create('field_service_backup_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('field_service_orders')->cascadeOnDelete();
                $table->foreignId('primary_technician_id')->constrained('field_service_technicians')->cascadeOnDelete();
                $table->foreignId('assistant_technician_id')->nullable()->constrained('field_service_technicians')->nullOnDelete();
                $table->string('reason', 50)->default('heavy_lifting'); // heavy_lifting, height_work, specialized_tools, double_manpower
                $table->text('notes')->nullable();
                $table->string('status', 30)->default('pending'); // pending, accepted, on_site, completed, cancelled
                $table->decimal('assistant_labor_share_amount', 14, 2)->default(0);
                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_service_backup_requests');
    }
};

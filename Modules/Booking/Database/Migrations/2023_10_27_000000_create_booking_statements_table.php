<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingStatementsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('booking_statements')) {
            Schema::create('booking_statements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Creator
                $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('cascade'); // The main provider
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status')->default('draft'); // draft, approved, completed
                $table->json('roles_data')->nullable(); // Stores selected users for other roles: {role_id: user_id, ...}
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('booking_statements');
    }
}

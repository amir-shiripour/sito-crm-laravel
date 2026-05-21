<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use updateOrCreate to prevent duplicates on re-migration
        Category::updateOrCreate(
            ['title' => 'دسته مشتریان', 'type' => 'expense'],
            [
                'status' => 1,
                'is_system' => true, // Mark this as a system category
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Find and delete the specific system category
        $category = Category::where('title', 'دسته مشتریان')
            ->where('type', 'expense')
            ->where('is_system', true)
            ->first();

        if ($category) {
            $category->delete();
        }
    }
};

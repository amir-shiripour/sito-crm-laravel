<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $table = 'clients';
    private string $column = 'case_number';
    private string $index  = 'clients_case_number_index';

    private function hasIndex(string $table, string $indexName): bool
    {
        $row = DB::selectOne(
            "SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1",
            [$table, $indexName]
        );

        return (bool) $row;
    }

    public function up(): void
    {
        // 1) ستون را اضافه کن (nullable => آسیبی به داده‌های قبلی نمی‌زند)
        if (!Schema::hasColumn($this->table, $this->column)) {
            Schema::table($this->table, function (Blueprint $table) {
                // برای MySQL (خصوصاً 8+) بهتره ستون آخر جدول اضافه بشه (احتمال INSTANT بیشتر)
                $table->string($this->column, 100)->nullable();
            });
        }

        // 2) ایندکس را اگر وجود ندارد بساز
        if (!$this->hasIndex($this->table, $this->index)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->index($this->column, $this->index);
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex($this->table, $this->index)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropIndex($this->index);
            });
        }

        if (Schema::hasColumn($this->table, $this->column)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn($this->column);
            });
        }
    }
};

<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SlugService
{
    public static function generate(string $title, string $table, ?int $ignoreId = null): string
    {
        $base = Str::slug($title, '-');
        if (empty($base)) {
            // Fallback for non-english slugification (Persian/Arabic titles)
            $base = str_replace(' ', '-', trim($title));
            $base = preg_replace('/[^A-Za-z0-9\x{0600}-\x{06FF}-]/u', '', $base);
            $base = preg_replace('/-+/', '-', $base);
            $base = trim($base, '-');
        }

        $slug = $base;
        $count = 1;

        while (DB::table($table)
            ->where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}

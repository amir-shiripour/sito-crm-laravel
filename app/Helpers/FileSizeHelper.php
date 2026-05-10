<?php

namespace App\Helpers;

class FileSizeHelper
{
    /**
     * Convert bytes to a human-readable format.
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function humanFilesize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes == 0) {
            return '0 ' . $units[0];
        }

        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

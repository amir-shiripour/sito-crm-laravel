<?php

namespace App\Traits;

use App\Services\ImageOptimizerService;

trait FileUploadTrait
{
    /**
     * Upload and optionally optimize a file.
     *
     * @param mixed $file
     * @param string $directory
     * @param string $disk
     * @return string
     */
    protected function uploadFile($file, string $directory, string $disk = 'public'): string
    {
        $optimizer = app(ImageOptimizerService::class);
        return $optimizer->uploadAndOptimize($file, $directory, $disk);
    }
}

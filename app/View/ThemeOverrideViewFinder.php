<?php

declare(strict_types=1);

namespace App\View;

use App\Services\ViewOverrideResolver;
use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;

class ThemeOverrideViewFinder extends FileViewFinder
{
    protected ?ViewOverrideResolver $resolver = null;

    public function setResolver(ViewOverrideResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * پیدا کردن مسیر فیزیکی فایل View و چک کردن Override برای مشتری فعال.
     */
    public function find($name)
    {
        if ($this->resolver) {
            $overridePath = $this->resolver->findOverridePath($name);
            if ($overridePath) {
                return $overridePath;
            }
        }

        return parent::find($name);
    }
}

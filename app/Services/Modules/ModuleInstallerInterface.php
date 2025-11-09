<?php

namespace App\Services\Modules;

interface ModuleInstallerInterface
{
    public function install(): void;
    public function enable(): void;
    public function disable(): void;
    public function reset(): void;
    public function uninstall(): void;
}

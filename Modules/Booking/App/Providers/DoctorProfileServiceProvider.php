<?php

namespace Modules\Booking\App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Booking\App\Models\DoctorMedia;
use Modules\Booking\App\Models\DoctorProfile;

class DoctorProfileServiceProvider extends ServiceProvider
{
    protected string $moduleName      = 'DoctorProfile';
    protected string $moduleNameLower = 'doctorprofile';

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerMigrations();

        View::composer('*', function ($view) {
            if ($view->getName() !== 'profile.show') {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            if (!$user->hasRole('doctor')) {
                return;
            }

            $userId  = $user->id;

            // firstOrCreate so doctor tab always shows even before profile is filled
            $profile = DoctorProfile::firstOrCreate(['user_id' => $userId]);

            $photos  = DoctorMedia::where('user_id', $userId)
                ->where('type', 'photo')->latest()->get();
            $videos  = DoctorMedia::where('user_id', $userId)
                ->where('type', 'video')->latest()->get();

            $view->with(compact('profile', 'photos', 'videos'));
        });
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(base_path('Modules/Booking/Routes/web.php'));
    }

    protected function registerViews(): void
    {
        $path = __DIR__ . '/../Resources/views';
        if (is_dir($path)) {
            $this->loadViewsFrom($path, $this->moduleNameLower);
        }
    }

    protected function registerMigrations(): void
    {
        $path = __DIR__ . '/../Database/Migrations';
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    public function register(): void
    {
        //
    }
}

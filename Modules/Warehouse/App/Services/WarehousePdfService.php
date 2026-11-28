<?php

namespace Modules\Warehouse\App\Services;

use Illuminate\Http\Response;
use Modules\Settings\Entities\Setting;
use Modules\Warehouse\Support\WarehouseUserVisibility;
use Spatie\Browsershot\Browsershot;

class WarehousePdfService
{
    /**
     * Render a Warehouse Blade view through the core CRM Browsershot PDF engine.
     */
    public function renderPdf(string $view, array $data, string $filename, array $options = []): string
    {
        @ini_set('memory_limit', '512M');

        [$siteName, $logoDataUri] = $this->resolveCoreBranding();
        $generatedAtJalali = function_exists('jdate')
            ? jdate('now')->format('Y/m/d — H:i')
            : now()->format('Y-m-d H:i');

        $payload = array_merge([
            'siteName' => $siteName,
            'logoDataUri' => $logoDataUri,
            'generatedAtJalali' => $generatedAtJalali,
            'generatedBy' => WarehouseUserVisibility::displayUserName(auth()->user()),
            'landscape' => (bool)($options['landscape'] ?? false),
        ], $data);

        $html = view($view, $payload)->render();

        $browsershot = Browsershot::html($html);

        if (PHP_OS_FAMILY === 'Windows') {
            $browsershot->setNodeBinary(env('NODE_PATH', 'C:\\Program Files\\nodejs\\node.exe'))
                ->setNpmBinary(env('NPM_PATH', 'C:\\Program Files\\nodejs\\npm.cmd'))
                ->setChromePath(env('CHROME_PATH', 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'));
        } else {
            $browsershot->noSandbox()
                ->setOption('args', ['--disable-web-security'])
                ->setNodeModulePath(base_path('node_modules'))
                ->setChromePath(env('CHROME_PATH', '/usr/bin/google-chrome'));
        }

        $browsershot->format($options['format'] ?? 'A4')
            ->margins(10, 10, 12, 10)
            ->showBackground()
            ->waitUntilNetworkIdle();

        if (!empty($options['landscape'])) {
            $browsershot->landscape();
        }

        return $browsershot->pdf();
    }

    /**
     * Build inline HTTP PDF response.
     */
    public function downloadInline(string $view, array $data, string $filename, array $options = []): Response
    {
        $pdfBinary = $this->renderPdf($view, $data, $filename, $options);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $filename);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
        ]);
    }

    protected function resolveCoreBranding(): array
    {
        $siteName = (string)config('app.name', '');
        $logoDataUri = null;

        try {
            if (class_exists(Setting::class)) {
                $settings = Setting::pluck('value', 'key')->toArray();

                // Priority matches /settings#general ("عنوان سایت" -> app_name)
                foreach (['app_name', 'identity_site_name', 'site_name', 'identity_name'] as $k) {
                    if (!empty($settings[$k]) && trim((string)$settings[$k]) !== '') {
                        $siteName = trim((string)$settings[$k]);
                        break;
                    }
                }

                foreach (['app_logo', 'site_logo', 'identity_logo', 'company_logo', 'logo'] as $k) {
                    if (!empty($settings[$k]) && trim((string)$settings[$k]) !== '') {
                        $logoDataUri = $this->resolveLogoToDataUri((string)$settings[$k]);
                        if ($logoDataUri) {
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Keep graceful defaults if settings table is inaccessible
        }

        return [$siteName, $logoDataUri];
    }

    protected function resolveLogoToDataUri(string $logoPath): ?string
    {
        if (str_starts_with($logoPath, 'data:image/')) {
            return $logoPath;
        }

        $cleanPath = ltrim($logoPath, '/');
        $candidates = [
            public_path($cleanPath),
            storage_path('app/public/' . preg_replace('/^storage\//', '', $cleanPath)),
            public_path('storage/' . preg_replace('/^storage\//', '', $cleanPath)),
        ];

        foreach ($candidates as $file) {
            if (is_file($file) && is_readable($file)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'svg' => 'image/svg+xml',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
            }
        }

        return null;
    }
}

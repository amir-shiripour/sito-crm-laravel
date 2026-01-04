<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // فیلتر کردن خطاهای 404 معمولی
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                // خطاهای 404 را فقط در حالت development لاگ می‌کنیم
                if (!app()->environment('production')) {
                    \Illuminate\Support\Facades\Log::debug('[EXCEPTION] 404 - مسیر پیدا نشد', [
                        'message' => $e->getMessage()
                    ]);
                }
                return; // از لاگ کردن بیشتر جلوگیری می‌کنیم
            }

            // برای خطاهای دیگر، لاگ می‌کنیم
            \Illuminate\Support\Facades\Log::error('[EXCEPTION] خطا رخ داد', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // فیلتر کردن خطاهای 404 معمولی (درخواست‌های اسکن/ربات)
        if ($this->isHttpException($e) && $e->getStatusCode() === 404) {
            $path = $request->path();

            // اگر مسیر نامعتبر و کوتاه است (احتمالاً اسکن/ربات)، فقط در حالت development لاگ می‌کنیم
            if (strlen($path) < 10 && !app()->environment('production')) {
                \Illuminate\Support\Facades\Log::debug('[EXCEPTION] 404 - مسیر نامعتبر', [
                    'path' => $path,
                    'url' => $request->fullUrl()
                ]);
            }
        } else {
            // برای خطاهای دیگر (غیر از 404) یا در حالت development، لاگ می‌کنیم
            \Illuminate\Support\Facades\Log::error('[EXCEPTION RENDER] خطا در render', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'is_http_exception' => $this->isHttpException($e),
                'status_code' => $this->isHttpException($e) ? $e->getStatusCode() : null,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return parent::render($request, $e);
    }
}

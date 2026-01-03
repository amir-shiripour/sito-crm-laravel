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
            // لاگ کردن تمام خطاها برای debugging
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
        // لاگ کردن خطا قبل از render - همیشه لاگ می‌کنیم برای debugging
        \Illuminate\Support\Facades\Log::error('[EXCEPTION RENDER] خطا در render', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'is_http_exception' => $this->isHttpException($e),
            'trace' => $e->getTraceAsString()
        ]);

        return parent::render($request, $e);
    }
}

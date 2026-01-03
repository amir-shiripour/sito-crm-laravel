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
        // لاگ کردن خطا قبل از render
        if (!$this->isHttpException($e) && config('app.debug')) {
            \Illuminate\Support\Facades\Log::error('[EXCEPTION RENDER] خطا در render', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
            ]);
        }

        return parent::render($request, $e);
    }
}

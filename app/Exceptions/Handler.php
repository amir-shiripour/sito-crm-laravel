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
            $req = request();
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                \Illuminate\Support\Facades\Log::warning('[404 NOT FOUND]', [
                    'url' => $req ? $req->fullUrl() : null,
                    'path' => $req ? $req->path() : null,
                    'method' => $req ? $req->method() : null,
                    'ip' => $req ? $req->ip() : null,
                    'user_id' => auth()->id(),
                    'client_id' => auth('client')->id(),
                    'message' => $e->getMessage(),
                ]);
                return;
            }

            if (!$this->shouldReport($e)) {
                return;
            }

            \Illuminate\Support\Facades\Log::error('[EXCEPTION] خطا رخ داد', [
                'url' => $req ? $req->fullUrl() : null,
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
        \Illuminate\Support\Facades\Log::warning('[EXCEPTION RENDER]', [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'status' => $this->isHttpException($e) ? $e->getStatusCode() : 500,
            'url' => $request ? $request->fullUrl() : null,
            'path' => $request ? $request->path() : null,
            'method' => $request ? $request->method() : null,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return parent::render($request, $e);
    }
}

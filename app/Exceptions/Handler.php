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
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Tangani CSRF / Token Mismatch secara anggun tanpa menampilkan layar putih 419 Page Expired
        if ($e instanceof \Illuminate\Session\TokenMismatchException || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Sesi Anda telah kedaluwarsa. Silakan refresh halaman dan coba lagi.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            if (auth()->check()) {
                return redirect()->back()->withInput()->with('error', 'Sesi Anda telah kedaluwarsa atau halaman terbuka terlalu lama. Token telah diperbarui, silakan coba kirim ulang.');
            }

            return redirect()->route('login')->with('error', 'Sesi login Anda telah berakhir karena tidak aktif. Silakan masuk kembali.');
        }

        return parent::render($request, $e);
    }
}

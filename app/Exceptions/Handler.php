<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
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
//
//    public function render($request, Throwable $exception)
//    {
//        // 捕获Token鉴权失败的异常
//        if ($exception instanceof AuthenticationException) {
//            return response()->json(['error' => 'Unauthenticated'], 401);
//        }
//
//        return parent::render($request, $exception);
//    }
}

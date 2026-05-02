<?php

use App\Models\AppErrorLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\CurrentPropertyMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e) {
            $request = request();

            Log::channel('errors')->error($e->getMessage(), [
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'user_id' => $request?->user()?->id,
            ]);

            try {
                AppErrorLog::create([
                    'level' => 'error',
                    'exception' => $e::class,
                    'message' => $e->getMessage() ?: 'Unhandled exception',
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'method' => $request?->method(),
                    'url' => $request?->fullUrl(),
                    'user_id' => $request?->user()?->id,
                    'ip_address' => $request?->ip(),
                    'user_agent' => $request?->userAgent(),
                    'context' => [
                        'route' => $request?->route()?->getName(),
                        'input_keys' => array_keys($request?->except(['password', 'password_confirmation', 'token', '_token']) ?? []),
                    ],
                ]);
            } catch (\Throwable $logException) {
                Log::channel('errors')->error('Failed to write exception to database', [
                    'exception' => $logException::class,
                    'message' => $logException->getMessage(),
                ]);
            }
        });

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'ไฟล์หรือข้อมูลที่ส่งมีขนาดใหญ่เกินไป กรุณาเลือกไฟล์ไม่เกิน 10MB ต่อไฟล์ แล้วลองใหม่อีกครั้ง';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 413);
            }

            return back()
                ->withInput($request->except(array_keys($request->files->all())))
                ->with('upload_too_large', $message);
        });
    })->create();

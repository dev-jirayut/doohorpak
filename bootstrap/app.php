<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

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

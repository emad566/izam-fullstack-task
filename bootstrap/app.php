<?php

use App\Http\Middleware\ApiLocalization;
use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware([ApiLocalization::class, 'web'])
                ->group(base_path('routes/web.php'));

            Route::middleware([ApiLocalization::class])
                ->prefix('api/auth/')
                ->group(base_path('routes/auth.php'));

            Route::middleware([ApiLocalization::class])
                ->prefix('api/')
                ->group(base_path('routes/api.php'));


        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // UnAuth Error Handler
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                $baseApiController = new BaseController();
                return $baseApiController->sendResponse(false, [

                ], "Unauthenticated", [], 401);
            }
        });

        // Not Found Exception Error Handler
        $exceptions->render(function (NotFoundHttpException  $e, Request $request) {
            if ($request->expectsJson()) {
                $baseApiController = new BaseController();
                return $baseApiController->sendResponse(false, [

                ], "EndPoint Is Not Found: " . $request->uri(), [], 404);
            }
        });
    })->create();

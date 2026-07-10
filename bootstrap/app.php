<?php

use App\Http\Middleware\CheckServiceAccess;
use App\Http\Middleware\CheckUserType;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        [
            'middleware' => ['auth:sanctum'],
            'prefix' => 'api',
        ],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => CheckUserType::class,
            'service_access' => CheckServiceAccess::class,

            // API Middleware
            'role.api' => App\Http\Middleware\Api\CheckUserType::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return;
            }

            $modelClass = $e->getModel();
            $message = method_exists($modelClass, 'notFoundMessage')
                ? $modelClass::notFoundMessage()
                : 'Record not found.';

            return response()->json(['success' => false, 'message' => $message], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return;
            }

            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                $modelClass = $previous->getModel();
                $message = method_exists($modelClass, 'notFoundMessage')
                    ? $modelClass::notFoundMessage()
                    : 'Record not found.';

                return response()->json(['success' => false, 'message' => $message], 404);
            }

            return response()->json(['success' => false, 'message' => 'Route not found.'], 404);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return;
            }

            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return;
            }

            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return;
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        });

    })->create();

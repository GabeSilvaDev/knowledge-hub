<?php

use App\Exceptions\ResourceNotFoundException;
use App\Http\Middleware\CheckRevokedToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            CheckRevokedToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $model = class_basename($e->getModel());
                throw new ResourceNotFoundException($model);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $previous = $e->getPrevious();

                if ($previous instanceof ModelNotFoundException) {
                    $model = class_basename($previous->getModel());
                    throw new ResourceNotFoundException($model);
                }

                return response()->json([
                    'message' => 'A rota solicitada não foi encontrada.',
                    'error' => 'Route not found',
                ], JsonResponse::HTTP_NOT_FOUND);
            }
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Ocorreu um erro inesperado no servidor.',
                    'error' => class_basename($e),
                ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        });

    })
    ->create();

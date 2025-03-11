<?php

use App\Exceptions\ClientErrorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->renderable(function (NotFoundHttpException $e) {
            return errorResponse('Route not found', Response::HTTP_NOT_FOUND);
        });

        $exceptions->renderable(function (ModelNotFoundException $e) {
            $modelName = class_basename($e->getModel());
            return errorResponse("$modelName not found", Response::HTTP_NOT_FOUND);
        });

        $exceptions->renderable(function (ValidationException $e) {
            return errorResponse($e->validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY, $e->validator->errors());
        });

        $exceptions->renderable(function (ClientErrorException $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        });

        $exceptions->renderable(function (Exception $e) {
            return errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        });

    })->create();

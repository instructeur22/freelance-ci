<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e): JsonResponse|\Illuminate\Http\Response
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                "message" => "Validation failed",
                "errors" => $e->errors(),
            ], 422);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json(["message" => "Resource not found"], 404);
        }

        if (config("app.debug")) {
            return parent::render($request, $e);
        }

        return response()->json(["message" => "Internal server error"], 500);
    }
}

<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;


if (! function_exists('successResponse')) {
    /**
     * Return a standard success json response
     */
    function successResponse($data = [], int $code = 200, $message = "Success") : JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message
        ], $code);
    }
}

if (! function_exists('errorResponse')) {
    /**
     * Return a standard error json response
     */
    function errorResponse(string $message, int $code = 400, MessageBag $errors = null) : JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}


if (! function_exists('slugify')) {
    /**
     * Return slug
     */
    function slugify(string $title, int $word = 8): string
    {
        return $title ? Str::slug(implode(' ', array_slice(explode(' ', $title), 0, $word))) : '';
    }

}

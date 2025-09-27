<?php

namespace CF\HelloWorld\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HelloWorldController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'Hello World from CF Package 🚀  '
        ]);
    }
}
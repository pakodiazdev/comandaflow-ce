<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SwaggerJsonController extends Controller
{
    public function __invoke()
    {
        $jsonPath = storage_path('api-docs/api-docs.json');
        
        if (file_exists($jsonPath)) {
            return response()->file($jsonPath, [
                'Content-Type' => 'application/json'
            ]);
        }
        
        return response()->json([
            'error' => 'Documentation not found'
        ], 404);
    }
}
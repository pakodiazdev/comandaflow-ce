<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SwaggerAssetController extends Controller
{
    public function __invoke(Request $request, $asset)
    {
        $path = base_path('vendor/swagger-api/swagger-ui/dist/' . $asset);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        $extension = pathinfo($asset, PATHINFO_EXTENSION);
        
        $contentType = match ($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'json' => 'application/json',
            default => 'application/octet-stream'
        };
        
        return response()->file($path, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
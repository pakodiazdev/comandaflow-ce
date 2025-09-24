<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SwaggerUIController extends Controller
{
    public function __invoke(Request $request)
    {
        $documentation = 'default';
        $urlsToDocs = [
            'ComandaFlow CE API' => url('/api/docs.json')
        ];
        $documentationTitle = 'ComandaFlow CE API Documentation';
        $useAbsolutePath = true;

        return view('l5-swagger::index', [
            'documentation' => $documentation,
            'urlsToDocs' => $urlsToDocs,
            'documentationTitle' => $documentationTitle,
            'useAbsolutePath' => $useAbsolutePath
        ]);
    }
}
<?php

namespace CF\HelloWorld\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Hello World",
 *     description="Endpoints relacionados con Hello World"
 * )
 */
class HelloWorldController
{
    /**
     * @OA\Get(
     *     path="/hello",
     *     summary="Obtener mensaje de Hello World",
     *     description="Retorna un mensaje de saludo desde el paquete CF",
     *     operationId="getHelloWorld",
     *     tags={"Hello World"},
     *     @OA\Response(
     *         response=200,
     *         description="Mensaje de saludo exitoso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Hello World from CF Package 🚀",
     *                 description="Mensaje de saludo"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Internal Server Error"
     *             )
     *         )
     *     )
     * )
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'Hello World from CF Package 🚀  '
        ]);
    }
}
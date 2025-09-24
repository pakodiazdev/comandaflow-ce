<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * @OA\Tag(
 *     name="Health",
 *     description="Health check endpoints"
 * )
 */
class HealthController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/health",
     *      operationId="getHealthStatus",
     *      tags={"Health"},
     *      summary="Get system health status",
     *      description="Returns the health status of all system services including database and cache",
     *      @OA\Response(
     *          response=200,
     *          description="System is healthy",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="healthy"),
     *              @OA\Property(property="timestamp", type="string", format="date-time", example="2025-09-19T12:00:00Z"),
     *              @OA\Property(
     *                  property="services",
     *                  type="object",
     *                  @OA\Property(
     *                      property="database",
     *                      type="object",
     *                      @OA\Property(property="status", type="string", example="healthy"),
     *                      @OA\Property(property="connection", type="string", example="pgsql"),
     *                      @OA\Property(property="response_time_ms", type="number", format="float", example=15.07)
     *                  ),
     *                  @OA\Property(
     *                      property="cache",
     *                      type="object",
     *                      @OA\Property(property="status", type="string", example="healthy"),
     *                      @OA\Property(property="driver", type="string", example="database"),
     *                      @OA\Property(property="response_time_ms", type="number", format="float", example=2.1)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=503,
     *          description="System is unhealthy",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="unhealthy"),
     *              @OA\Property(property="timestamp", type="string", format="date-time", example="2025-09-19T12:00:00Z"),
     *              @OA\Property(
     *                  property="services",
     *                  type="object",
     *                  @OA\Property(
     *                      property="database",
     *                      type="object",
     *                      @OA\Property(property="status", type="string", example="unhealthy"),
     *                      @OA\Property(property="error", type="string", example="Connection failed")
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function check(): JsonResponse
    {
        $services = [];
        $overallStatus = 'healthy';

        $services['database'] = $this->checkDatabase();
        if ($services['database']['status'] === 'unhealthy') {
            $overallStatus = 'unhealthy';
        }

        $services['cache'] = $this->checkCache();
        if ($services['cache']['status'] === 'unhealthy') {
            $overallStatus = 'unhealthy';
        }

        $response = [
            'status' => $overallStatus,
            'timestamp' => now()->toISOString(),
            'services' => $services,
        ];

        $statusCode = $overallStatus === 'healthy' ? 200 : 503;

        return response()->json($response, $statusCode);
    }

    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'connection' => config('database.default'),
                'response_time_ms' => $responseTime,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'connection' => config('database.default'),
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            Cache::put($testKey, $testValue, 60);
            $retrievedValue = Cache::get($testKey);
            Cache::forget($testKey);
            
            if ($retrievedValue !== $testValue) {
                throw new Exception('Cache read/write test failed');
            }
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'driver' => config('cache.default'),
                'response_time_ms' => $responseTime,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'driver' => config('cache.default'),
                'error' => $e->getMessage(),
            ];
        }
    }
}
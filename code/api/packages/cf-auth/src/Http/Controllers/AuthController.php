<?php

namespace CF\CE\Auth\Http\Controllers;

use CF\CE\Auth\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Shared authentication endpoints that work in both Central and Tenant contexts. The database context (Central or Tenant) is automatically determined by the domain making the request."
 * )
 */
class AuthController
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     operationId="authLogin",
     *     tags={"Authentication"},
     *     summary="User login (Shared: Central & Tenant)",
     *     description="Authenticate user and get access token. Works for both Central and Tenant domains. The context is automatically determined by the requesting domain.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@comandaflow.com"),
     *             @OA\Property(property="password", type="string", format="password", example="admin123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Admin User"),
     *                     @OA\Property(property="email", type="string", example="admin@comandaflow.com"),
     *                     @OA\Property(property="description", type="string", example="System Administrator"),
     *                     @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="users.view"))
     *                 ),
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=1296000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials"),
     *             @OA\Property(property="errors", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * User login endpoint
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Get the correct user provider based on context
        $provider = $this->getUserProvider();
        
        // Manually find the user and check credentials
        $user = $provider->retrieveByCredentials($credentials);
        
        if (!$user || !$provider->validateCredentials($user, $credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => []
            ], 401);
        }

        $token = $user->createToken('CF Auth Token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'description' => $user->description,
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 15 * 24 * 60 * 60,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     operationId="authRegister",
     *     tags={"Authentication"},
     *     summary="User registration (Shared: Central & Tenant)",
     *     description="Register a new user account. Works for both Central and Tenant domains. The context is automatically determined by the requesting domain.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="description", type="string", example="Regular user", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="description", type="string", example="Regular user"),
     *                     @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *                 ),
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=1296000)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     *
     * User registration endpoint
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'description' => $request->description,
        ]);

        $token = $user->createToken('CF Auth Token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'description' => $user->description,
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 15 * 24 * 60 * 60,
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     operationId="authLogout",
     *     tags={"Authentication"},
     *     summary="User logout (Shared: Central & Tenant)",
     *     description="Revoke current user's access token. Works for both Central and Tenant contexts.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout successful")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * User logout endpoint
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->token();
        $token->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     operationId="authMe",
     *     tags={"Authentication"},
     *     summary="Get current user (Shared: Central & Tenant)",
     *     description="Get authenticated user's profile and permissions. Works for both Central and Tenant contexts.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Admin User"),
     *                 @OA\Property(property="email", type="string", example="admin@comandaflow.com"),
     *                 @OA\Property(property="description", type="string", example="System Administrator"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="users.view"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * Get current user endpoint
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'description' => $user->description,
                'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     operationId="authRefresh",
     *     tags={"Authentication"},
     *     summary="Refresh access token (Shared: Central & Tenant)",
     *     description="Revoke current token and generate a new one. Works for both Central and Tenant contexts.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=1296000)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * Refresh token endpoint
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->token()->revoke();
        
        // Create new token
        $token = $user->createToken('CF Auth Token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 15 * 24 * 60 * 60,
            ]
        ]);
    }

    /**
     * Determine the correct authentication guard based on tenancy context
     */
    private function getAuthGuard(): string
    {
        // Get the current host from the request
        $host = request()->getHost();
        
        // Central domains (you may need to adjust this list)
        $centralDomains = [
            'localhost', 
            'comandaflow.local',
            '127.0.0.1'
        ];
        
        // If it's not a central domain, it's a tenant domain
        if (!in_array($host, $centralDomains)) {
            return 'tenant';
        }
        
        return 'central';
    }

    /**
     * Get the correct user provider based on tenancy context
     */
    private function getUserProvider()
    {
        $guard = $this->getAuthGuard();
        
        if ($guard === 'tenant') {
            return Auth::createUserProvider('tenant_users');
        } else {
            return Auth::createUserProvider('central_users');
        }
    }
}

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
 *     description="Endpoints for user authentication and authorization"
 * )
 */
class AuthController
{
    /**
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

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => []
            ], 401);
        }

        $user = Auth::user();
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
}

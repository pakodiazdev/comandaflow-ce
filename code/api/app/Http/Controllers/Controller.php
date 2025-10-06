<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="ComandaFlow CE API",
 *     version="1.0.0",
 *     description="ComandaFlow Community Edition API Documentation - Centralized authentication and user management with Laravel Passport and role-based permissions",
 *     @OA\Contact(
 *         email="dev@comandaflow.com",
 *         name="ComandaFlow Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="ComandaFlow CE API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT Bearer token obtained from login endpoint"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication and authorization endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="User Management",
 *     description="User management endpoints (requires owner/manager role)"
 * )
 * 
 * @OA\Tag(
 *     name="Role Management", 
 *     description="Role and permission management endpoints (requires owner role)"
 * )
 */
abstract class Controller
{
    //
}

<?php

namespace CF\CE\Auth\Http\Controllers;

use CF\CE\Auth\Models\Role;
use CF\CE\Auth\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Role Management",
 *     description="Endpoints for role and permission management"
 * )
 */
class RoleController
{
    /**
     * @OA\Get(
     *     path="/roles",
     *     summary="Get all roles",
     *     description="Retrieve all available roles with their permissions",
     *     operationId="getRoles",
     *     tags={"Role Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Manager"),
     *                 @OA\Property(property="code", type="string", example="manager"),
     *                 @OA\Property(property="description", type="string", example="Responsible for daily operations"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="manage_tables"),
     *                     @OA\Property(property="code", type="string", example="manage_tables")
     *                 ))
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'success' => true,
            'data' => $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'code' => $role->code,
                    'description' => $role->description,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'code' => $permission->code ?? $permission->name,
                        ];
                    }),
                ];
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/roles/{code}",
     *     summary="Get role by code",
     *     description="Retrieve a specific role by its code",
     *     operationId="getRole",
     *     tags={"Role Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Role code",
     *         required=true,
     *         @OA\Schema(type="string", example="manager")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Manager"),
     *                 @OA\Property(property="code", type="string", example="manager"),
     *                 @OA\Property(property="description", type="string", example="Responsible for daily operations"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="manage_tables"),
     *                     @OA\Property(property="code", type="string", example="manage_tables")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     )
     * )
     */
    public function show(string $code): JsonResponse
    {
        $role = Role::with('permissions')->where('code', $code)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'code' => $role->code,
                'description' => $role->description,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'code' => $permission->code ?? $permission->name,
                    ];
                }),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/roles",
     *     summary="Create new role",
     *     description="Create a new role with permissions",
     *     operationId="createRole",
     *     tags={"Role Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","code"},
     *             @OA\Property(property="name", type="string", example="Custom Role"),
     *             @OA\Property(property="code", type="string", example="custom_role"),
     *             @OA\Property(property="description", type="string", example="Custom role description"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"manage_tables", "process_payments"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Custom Role"),
     *                 @OA\Property(property="code", type="string", example="custom_role"),
     *                 @OA\Property(property="description", type="string", example="Custom role description"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"manage_tables", "process_payments"})
     *             )
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
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:roles,code',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::createWithCode([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'guard_name' => 'api'
        ]);

        // Assign permissions if provided
        if ($request->has('permissions') && is_array($request->permissions)) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'code' => $role->code,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ]
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/roles/{code}",
     *     summary="Update role",
     *     description="Update role information and permissions",
     *     operationId="updateRole",
     *     tags={"Role Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Role code",
     *         required=true,
     *         @OA\Schema(type="string", example="manager")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Manager"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"manage_tables", "process_payments"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Manager"),
     *                 @OA\Property(property="code", type="string", example="manager"),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string"), example={"manage_tables", "process_payments"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $code): JsonResponse
    {
        $role = Role::where('code', $code)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update role data
        if ($request->has('name')) {
            $role->name = $request->name;
        }
        if ($request->has('description')) {
            $role->description = $request->description;
        }

        $role->save();

        // Update permissions if provided
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'code' => $role->code,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/roles/{code}",
     *     summary="Delete role",
     *     description="Delete a role (only if no users are assigned to it)",
     *     operationId="deleteRole",
     *     tags={"Role Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Role code",
     *         required=true,
     *         @OA\Schema(type="string", example="custom_role")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Role not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Role cannot be deleted (users assigned)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete role: users are assigned to it")
     *         )
     *     )
     * )
     */
    public function destroy(string $code): JsonResponse
    {
        $role = Role::where('code', $code)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Check if any users have this role
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role: users are assigned to it'
            ], 409);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/permissions",
     *     summary="Get all permissions",
     *     description="Retrieve all available permissions",
     *     operationId="getPermissions",
     *     tags={"Role Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Permissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="manage_tables"),
     *                 @OA\Property(property="code", type="string", example="manage_tables"),
     *                 @OA\Property(property="description", type="string", example="Manage restaurant tables")
     *             ))
     *         )
     *     )
     * )
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => $permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'code' => $permission->code ?? $permission->name,
                    'description' => $permission->description,
                ];
            })
        ]);
    }
}

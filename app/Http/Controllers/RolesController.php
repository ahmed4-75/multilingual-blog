<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\PermissionsEnum;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;


class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:' . PermissionsEnum::VIEW_ROLES->value)->only('index');
        $this->middleware('permission:' . PermissionsEnum::CREATE_ROLE->value)->only('store');
        $this->middleware('permission:' . PermissionsEnum::UPDATE_ROLE->value)->only('update');
        $this->middleware('permission:' . PermissionsEnum::DELETE_ROLE->value)->only('destroy');
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     tags={"Authorization"},
     *     summary="Roles and all available Permissions.",
     *     description="Show all Roles with their Permissions, and all available Permissions.",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="All Roles and Their Permissions and All Available Permissions"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="roles",type="array",@OA\Items(ref="#/components/schemas/RoleResource")),),
     *                 @OA\Property(property="permissions",type="array",@OA\Items(ref="#/components/schemas/PermissionResource"))
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized Action"
     *     )
     * )
    */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::get();
        return response()->json([
            'status' => 'Success',
            'message' => 'All Roles and Their Permissions and All Available Permissions',
            'data' => [
                'roles' => RoleResource::collection($roles),
                'permissions' => PermissionResource::collection($permissions)
            ]
        ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create Role",
     *     tags={"Authorization"},
     *     description="Authenticated user Create Role",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=50, description="unique in table roles,name", example="admin")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=201,
     *         description="store role",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Role Added Successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="Store role unauthorized",
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
    */
    public function store(CreateRoleRequest $request)
    {
        Role::create($request->validated());

        return response()->json([
            'status' => 'Success',
            'message' => 'Role Added Successfully'
        ],201);
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{role}",
     *     summary="Update Role",
     *     tags={"Authorization"},
     *     description="Authenticated user Update Role",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Parameter(name="role",in="path",required=true,description="Role id", @OA\Schema(type="integer", example=1)),
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","permissions"},
     *             @OA\Property(property="name", type="string", maxLength=50, description="unique in table roles,name except this name", example="admin"),
     *             @OA\Property(property="permissions",type="array",description="Array of permission IDs exists in table permissions,id",@OA\Items(type="integer",example=1))
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="update role",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Role Edited Successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="Update role Unauthorized",
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
    */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update($request->validated());
        $role->permissions()->sync($request->permissions);

        return response()->json([
            'status' => 'Success',
            'message' => 'Role Edited Successfully'
        ],200);
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{role}",
     *     summary="Delete Role",
     *     tags={"Authorization"},
     *     description="Authenticated user delete Role",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Parameter(name="role",in="path",required=true,description="Role id", @OA\Schema(type="integer", example=1)),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="delete role",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Role Deleted Successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="delete role Unauthorized "
     *     )
     * )
    */
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json([
            'status' => 'Success',
            'message' => 'Role Deleted successfully'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChangeRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Authorization"},
     *     summary="Users and all available roles.",
     *     description="Show all Users with their user roles and permissions, and all available roles.",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="All Users and Their Roles and All Available Roles"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="object",
     *                     @OA\Property(property="data",type="array",@OA\Items(ref="#/components/schemas/UserResource")),
     *                     @OA\Property(
     *                         property="links",
     *                         type="object",
     *                         @OA\Property(property="first", type="string", example="http://example.com/api/posts?page=1"),
     *                         @OA\Property(property="last", type="string", example="http://example.com/api/posts?page=5"),
     *                         @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                         @OA\Property(property="next", type="string", nullable=true, example="http://example.com/api/posts?page=2")
     *                     ),
     *                     @OA\Property(
     *                         property="meta",
     *                         type="object",
     *                         @OA\Property(property="current_page", type="integer", example=1),
     *                         @OA\Property(property="from", type="integer", example=1),
     *                         @OA\Property(property="last_page", type="integer", example=5),
     *                         @OA\Property(property="path", type="string", example="http://example.com/api/posts"),
     *                         @OA\Property(property="per_page", type="integer", example=15),
     *                         @OA\Property(property="to", type="integer", example=15),
     *                         @OA\Property(property="total", type="integer", example=50)
     *                     ),
     *                 ),
     *                 @OA\Property(property="roles",type="array",@OA\Items(ref="#/components/schemas/RoleResource"))
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
        $users = User::withTrashed()->with([
            'roles:id,name',
            'roles.permissions'
        ])->paginate(15);
        $roles = Role::get();
        return response()->json([
            'status' => 'Success',
            'message' => 'All Users and Their Roles and All Available Roles',
            'data' => [
                'users' => UserResource::collection($users),
                'roles' => RoleResource::collection($roles)
            ]
        ],200);
    }

    /**
     * @OA\Put(
     *     path="/api/users/change-role/{user}",
     *     tags={"Authorization"},
     *     summary="Change User roles",
     *     description="Change User roles.",
     *     security={{"sanctum":{}}},
     *     
     *     @OA\Parameter(name="user",in="path",required=true,description="User ID",@OA\Schema(type="integer", example=1)),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role_ids"},
     *             @OA\Property(property="role_ids", type="array", description="exists in table roles,id",@OA\Items(type="integer", example=1))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Roles changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Roles Changed Successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="User is deactivated or Unauthorized Action",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Error"),
     *             @OA\Property(property="message", type="string", example="The change role is Prevented Because the user account is deactivated, 
     *                 or you do not have the permission to change User Role or change Role very important User.")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     * 
     * 
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
    */
    public function changeRole(ChangeRoleRequest $request,User $user)
    {
        $user->roles()->sync($request->role_ids);
    
        return response()->json([
            'status' => 'Success',
            'message' => 'Roles Changed Successfully'
        ],200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/ban/{id}",
     *     tags={"Authorization"},
     *     summary="Baning User",
     *     description="Baning specified User.",
     *     security={{"sanctum":{}}},
     *     
     *     @OA\Parameter(name="id",in="path",required=true,description="User ID",@OA\Schema(type="integer", example=1)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User is Banned",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="User is Banned Successfully")
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
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
    */
    public function banUser(string $id)
    {
        User::findOrFail($id)->delete();
        
        return response()->json([
            'status' => 'Success',
            'message' => 'User is Banned Successfully'
        ],200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/activate/{id}",
     *     tags={"Authorization"},
     *     summary="Activating User",
     *     description="Activating specified User.",
     *     security={{"sanctum":{}}},
     *     
     *     @OA\Parameter(name="id",in="path",required=true,description="User ID",@OA\Schema(type="integer", example=1)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User Activated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="User Activated Successfully")
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
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
    */
    public function activateUser(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'status' => 'Success',
            'message' => 'User Activated Successfully'
        ],200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/delete/{id}",
     *     tags={"Authorization"},
     *     summary="Delete User",
     *     description="Delete specified User.",
     *     security={{"sanctum":{}}},
     *     
     *     @OA\Parameter(name="id",in="path",required=true,description="User ID",@OA\Schema(type="integer", example=1)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User is Deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="User is Deleted Successfully")
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
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
    */
    public function destroyUser(string $id)
    {
        $user = User::findOrFail($id);
        $user->forceDelete();

        return response()->json([
            'status' => 'Success',
            'message' => 'User is Deleted Successfully'
        ],200);
    }
}

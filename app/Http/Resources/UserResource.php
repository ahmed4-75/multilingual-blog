<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer",example=1),
 *     @OA\Property(property="name", type="string", example="user name"),
 *     @OA\Property(property="email", type="string", example="name@test.com"),
 *     @OA\Property(property="phone", type="string", example="user phone"),
 *     @OA\Property(property="lang", type="string", example="user lang"),
 *     @OA\Property(property="favicon", type="string", example="user favicon"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="active", type="boolean", example=true),
 *     @OA\Property(property="posts", type="array", description="Loaded only if relation posts is loaded", @OA\Items(ref="#/components/schemas/PostResource")),
 *     @OA\Property(property="comments", type="array", description="Loaded only if relation comments is loaded", @OA\Items(ref="#/components/schemas/CommentResource")),
 *     @OA\Property(property="roles", type="array", description="Loaded only if relation roles is loaded", @OA\Items(ref="#/components/schemas/RoleResource")),
 *     @OA\Property(property="permissions", type="array", description="Loaded only if relation roles is loaded", @OA\Items(ref="#/components/schemas/PermissionResource"))
 * )
 */

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'lang' => $this->lang,
            'favicon' => $this->favicon,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'active' => $this->is_active,

            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'permissions' => $this->whenLoaded('roles', function () {return PermissionResource::collection($this->permissions()); })
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PermissionResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer",example=1),
 *     @OA\Property(property="name", type="string", example="role name"),
 *     @OA\Property(property="roles", type="array", description="Loaded only if relation roles is loaded", @OA\Items(ref="#/components/schemas/RoleResource"))
 * )
 */
class PermissionResource extends JsonResource
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

            'roles' => RoleResource::collection($this->whenLoaded('roles'))
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ReactResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer",example=1),
 *     @OA\Property(property="name", type="string", example="tag name"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="posts", type="array", description="Loaded only if relation posts is loaded", @OA\Items(ref="#/components/schemas/PostResource")),
 *     @OA\Property(property="comments", type="array", description="Loaded only if relation comments is loaded", @OA\Items(ref="#/components/schemas/CommentResource"))
 * )
 */

class ReactResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'comments' => CommentResource::collection($this->whenLoaded('comments'))
        ];
    }
}

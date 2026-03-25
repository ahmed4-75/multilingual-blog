<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PostResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer",example=1),
 *     @OA\Property(property="lang", type="string", example="user lang"),
 *     @OA\Property(property="body", type="string", example="post content"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time"),
 *     @OA\Property(property="user", description="Loaded only if relation user is loaded", ref="#/components/schemas/UserResource"),
 *     @OA\Property(property="comments", type="array", description="Loaded only if relation comments is loaded", @OA\Items(ref="#/components/schemas/CommentResource")),
 *     @OA\Property(property="reactos", type="array", description="Loaded only if relation reactos is loaded", @OA\Items(ref="#/components/schemas/ReactResource"))
 * )
 */

class PostResource extends JsonResource
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
            'lang' => $this->lang,
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            'user' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'reactos' => ReactResource::collection($this->whenLoaded('reactos'))
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReactCommentRequest;
use App\Http\Requests\ReactPostRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReactosController extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/reactos/post",
     *     summary="Set a React to a Post",
     *     tags={"My Reactos"},
     *     description="Choose or Change the React of a Post",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"react_id","post_id"},
     *             @OA\Property(property="react_id", type="integer", description="exists:reactos,id", example="1"),
     *             @OA\Property(property="post_id", type="integer", description="exists:posts,id", example="1")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Choose or Change the React of a Post",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="React set successfully"),

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
     *         description="Unauthorized Action"
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     ),
     * )
     */
    public function reactPost(ReactPostRequest $request)
    {
        $user = Auth::user();

        DB::transaction(function () use ( $request, $user) {
            $post = Post::where('id', $request->post_id)->lockForUpdate()->firstOrFail();
            # If there is an old attached react, it's detached to replace a new React .
            $post->reactos()->wherePivot('user_id', $user->id)->detach();
            $post->reactos()->attach($request->react_id, ['user_id' => $user->id]);
        });
    
        return response()->json([
            'status' => 'Success',
            'message' => 'React set successfully'
        ],200);
    }

    /**
     * @OA\POST(
     *     path="/api/reactos/comment",
     *     summary="Set a React to a Comment",
     *     tags={"My Reactos"},
     *     description="Choose or Change the React of a Comment",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"react_id","comment_id"},
     *             @OA\Property(property="react_id", type="integer", description="exists:reactos,id", example="1"),
     *             @OA\Property(property="comment_id", type="integer", description="exists:comments,id", example="1")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Choose or Change the React of a Comment",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="React set successfully"),

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
     *         description="Unauthorized Action"
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     ),
     * )
     */
    public function reactComment(ReactCommentRequest $request)
    {
        $user = Auth::user();

        DB::transaction(function () use ( $request, $user) {
            $comment = Comment::where('id', $request->comment_id)->lockForUpdate()->firstOrFail();
            # If there is an old attached react, it's detached to replace a new React .
            $comment->reactos()->wherePivot('user_id', $user->id)->detach();
            $comment->reactos()->attach($request->react_id, ['user_id' => $user->id]);
        });
    
        return response()->json([
            'status' => 'Success',
            'message' => 'React set successfully'
        ],200);
    }
}

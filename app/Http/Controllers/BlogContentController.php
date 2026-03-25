<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReactResource;
use App\Models\Post;
use App\Models\React;
use App\Models\User;

class BlogContentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/blog-content",
     *     summary="all Posts and all Comments and all available Reactos",
     *     tags={"Blog Content"},
     *     description="Get all posts and all comments connected to every post and reactos connected to every post and comment and all available reactos ",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="all Posts and all Comments and all available Reactos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="All Posts with the Comments and Reactos of every Post and Comment Retrieved Successfully, and all available Reactos"),
     *             @OA\Property(property="data",type="array",@OA\Items(ref="#/components/schemas/PostResource")),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://example.com/api/posts?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://example.com/api/posts?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://example.com/api/posts?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="path", type="string", example="http://example.com/api/posts"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             ),
     *             @OA\Property(
     *                 property="reactos",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ReactResource")
     *             )
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
     * )
     */
    public function index()
    {
        $allPosts = Post::with('user','comments.reactos','reactos')->paginate(15);
        $reactos = React::select('id','name')->get();

        return response( PostResource::collection($allPosts)->additional
            ([
                'status' => 'Success',
                'message' => 'All Posts with the Comments and Reactos of every Post and Comment Retrieved Successfully, and all available Reactos',
                'reactos' => ReactResource::collection($reactos)
            ])
        ,200);
    }

    /**
     * @OA\Get(
     *     path="/api/blog-content/search/{user_id}",
     *     summary="all Posts and all Comments and all of a User and all available Reactos",
     *     tags={"Blog Content"},
     *     description="Get all posts and all comments connected to every post, and reactos connected to every post and comment of a User and all available reactos.",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Parameter(name="user_id",in="path",required=true,description="User id", @OA\Schema(type="integer", example=1)),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Get all posts and all comments connected to every post, and reactos connected to every post and comment of a User and all available reactos.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="a User All Posts with the Comments and Reactos of every Post and Comment Retrieved Successfully, and all available Reactos"),
     *             @OA\Property(property="data",type="array",@OA\Items(ref="#/components/schemas/PostResource")),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://example.com/api/posts?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://example.com/api/posts?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://example.com/api/posts?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="path", type="string", example="http://example.com/api/posts"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             ),
     *             @OA\Property(
     *                 property="reactos",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ReactResource")
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     * )
     */
    public function getUserPosts(string $id)
    {
        $user = User::findOrFail($id);
        $posts = $user->posts()->with(['comments.reactos','reactos'])->paginate(15);
        $reactos = React::select('id','name')->get();

        return response( PostResource::collection($posts)->additional
            ([
                'status' => 'Success',
                'message' => 'a User All Posts with the Comments and Reactos of every Post and Comment Retrieved Successfully, and all available Reactos',
                'reactos' => ReactResource::collection($reactos)
            ])
        ,200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\PermissionsEnum;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReactResource;
use App\Models\Comment;
use App\Models\React;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:' . PermissionsEnum::VIEW_MY_POSTS->value)->only('index');
        $this->middleware('permission:' . PermissionsEnum::CREATE_MY_POST->value)->only('store');
    }
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="My all Posts and all available Reactos",
     *     tags={"My Posts"},
     *     description="Get all posts include Private posts of Authenticated user with comments, reactos, and available reactos",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of posts with comments, reactos and all reactos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="All Posts User with the comments and reactos of every Post Retrieved Successfully, and all available Reactos"),
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
     *         description="Show my posts from deactivated account",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Error"),
     *             @OA\Property(property="message", type="string", example="User Account is deactivated")
     *         )
     *     )
     * )
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->trashed()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'User Account is deactivated'
            ], 403);
        }
        $myPosts = $user->posts()->withTrashed()->with(['comments.reactos','reactos'])->paginate(15);
        $reactos = React::select('id','name')->get();

        return response( PostResource::collection($myPosts)->additional
            ([
                'status' => 'Success',
                'message' => 'All Posts User with the comments and reactos of every Post and Comment Retrieved Successfully, and all available Reactos',
                'reactos' => ReactResource::collection($reactos)
            ])
        ,200);
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create My Post",
     *     tags={"My Posts"},
     *     description="Authenticated user Create Post",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body","lang"},
     *             @OA\Property(property="body", type="string", example="anther post"),
     *             @OA\Property(property="lang", type="string", enum={"ar","en","ur","sp"}, example="ar")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=201,
     *         description="store post",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Post Created Successfully")
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
     *         description="Store post from deactivated account or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Error"),
     *             @OA\Property(property="message", type="string", example="User Account is deactivated or Unauthorized")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
    */
    public function store(CreatePostRequest $request)
    {
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->trashed()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'User Account is deactivated or Unauthorized'
            ], 403);
        }
        $user->posts()->create($request->validated());

        return response()->json([
            'status' => 'Success',
            'message' => 'Post Created Successfully'
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/posts/{post}",
     *     summary="Update My Post",
     *     tags={"My Posts"},
     *     description="Authenticated user update Post",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Parameter(name="post",in="path",required=true,description="Post id", @OA\Schema(type="integer", example=1)),
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body","lang"},
     *             @OA\Property(property="body", type="string", example="anther post"),
     *             @OA\Property(property="lang", type="string", enum={"ar","en","ur","sp"}, example="ar")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="update post",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Post Updated Successfully")
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
     *         description="Update post from deactivated account unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Error"),
     *             @OA\Property(property="message", type="string", example="User Account is deactivated or Unauthorized")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
    */
    public function update(UpdatePostRequest $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->trashed()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'User Account is deactivated or Unauthorized'
            ], 403);
        }
        $post = $user->posts()->findOrFail($id);
        $post->update($request->validated());

        return response()->json([
            'status' => 'Success',
            'message' => 'Post Updated Successfully'
        ],200);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{post}",
     *     summary="Delete My Post",
     *     tags={"My Posts"},
     *     description="Authenticated user delete Post",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Parameter(name="post",in="path",required=true,description="Post id", @OA\Schema(type="integer", example=1)),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="delete post",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Post Deleted Successfully")
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
     *         description="Post not found"
     *     )
     * )
    */
    public function destroy(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $post= $user->posts()->findOrFail($id);

        $post->reactos()->detach();
        DB::table('reactobles')
        ->where('reactoble_type', Comment::class)
        ->whereIn('reactoble_id', function ($query) use ($post) {
            $query->select('id')->from('comments')->where('post_id', $post->id);
        })
        ->delete();

        $post->forceDelete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Post Deleted Successfully'
        ],200);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/private/{post}",
     *     summary="Make My Post Private",
     *     tags={"My Posts"},
     *     description="Authenticated user Make the Post Private",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Parameter(name="post",in="path",required=true,description="Post id", @OA\Schema(type="integer", example=1)),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="delete post",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="The Post is Private Successfully")
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
     *         description="Post not found"
     *     )
     * )
    */
    public function PrivatePost(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $post= $user->posts()->findOrFail($id);
        $post->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'The Post is Private Successfully'
        ],200);
    }


    /**
     * @OA\Get(
     *     path="/api/posts/public/{post}",
     *     summary="Make My Post Public",
     *     tags={"My Posts"},
     *     description="Authenticated user Make the Post Public",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Parameter(name="post",in="path",required=true,description="Post id", @OA\Schema(type="integer", example=1)),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="restore post",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="The Post is Public Successfully")
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
     *         description="Post not found"
     *     )
     * )
    */
    public function publicPost(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $post= $user->posts()->withTrashed()->findOrFail($id);
        if (!$post) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Post not found'
            ], 404);
        }
        $post->restore();

        return response()->json([
            'status' => 'Success',
            'message' => 'The Post is Public Successfully'
        ],200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\PermissionsEnum;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ReactResource;
use App\Models\React;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:' . PermissionsEnum::VIEW_MY_COMMENTS->value)->only('index');
        $this->middleware('permission:' . PermissionsEnum::CREATE_MY_COMMENT->value)->only('store');
    }

    /**
     * @OA\Get(
     *     path="/api/comments",
     *     summary="My all Comments and all available Reactos",
     *     tags={"My Comments"},
     *     description="Get all comments of an authenticated user with:
     *                  - the post of each comment
     *                  - the post author
     *                  - reactos of the comment
     *                  - reactos of the post
     *                  - and all available reactos",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of comments with comment, reactos and all reactos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="All Comments User with the post of ever comment and reactos of every Comment Retrieved Successfully, and all available Reactos"),
     *             @OA\Property(property="data",type="array",@OA\Items(ref="#/components/schemas/CommentResource")),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string", example="http://example.com/api/comments?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://example.com/api/comments?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://example.com/api/comments?page=2")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="path", type="string", example="http://example.com/api/comments"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             ),
     *             @OA\Property(property="reactos",type="array",@OA\Items(ref="#/components/schemas/ReactResource"))
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
     *         description="Show my comments from deactivated account",
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
        $myComments = $user->comments()->with(['post.user','post.reactos','reactos'])->paginate(15);
        $reactos = React::select('id','name')->get();

        return response( CommentResource::collection($myComments)->additional
            ([
                'status' => 'Success',
                'message' => 'All Comments User with the post of ever comment and reactos of every Comment Retrieved Successfully, and all available Reactos',
                'reactos' => ReactResource::collection($reactos)
            ])
        ,200);
    }

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create My Comment",
     *     tags={"My Comments"},
     *     description="Authenticated user Create Comment",
     *     security={{"sanctum":{}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body","lang","post_id"},
     *             @OA\Property(property="body", type="string", example="anther post"),
     *             @OA\Property(property="lang", type="string", enum={"ar","en","ur","sp"}, example="ar"),
     *             @OA\Property(property="post_id", type="integer", description="exists('posts', 'id')", example="1")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=201,
     *         description="store comment",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Comment Created Successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="Store comment from deactivated account or unauthorized",
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
    public function store(CreateCommentRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->trashed()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'User Account is deactivated or Unauthorized'
            ], 403);
        }
        $user->comments()->create([
            'body'    => $request->body,
            'lang'    => $request->lang,
            'post_id' => $request->post_id
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Comment Created Successfully'
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
     *     path="/api/comments/{comment}",
     *     summary="Update My Comment",
     *     tags={"My Comments"},
     *     description="Authenticated user update Comment",
     *     @OA\Parameter(name="comments",in="path",required=true,description="Comment id", @OA\Schema(type="integer", example=1)),
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
     *         response=200,
     *         description="update comments",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Comment Updated Successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=403,
     *         description="Update comment from deactivated account or unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Error"),
     *             @OA\Property(property="message", type="string", example="User Account is deactivated or Unauthorized")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
    */
    public function update(UpdateCommentRequest $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->trashed()) {
            return response()->json([
                'status' => 'Error',
                'message' => 'User Account is deactivated or Unauthorized'
            ], 403);
        }
        $comment = $user->comments()->findOrFail($id);
        $comment->update($request->validated());

        return response()->json([
            'status' => 'Success',
            'message' => 'Comment Updated Successfully'
        ],200);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{comment}",
     *     summary="Delete My Comment",
     *     tags={"My Comments"},
     *     description="Authenticated user delete Comment",
     *     @OA\Parameter(name="comment",in="path",required=true,description="Comment id", @OA\Schema(type="integer", example=1)),
     *     security={{"sanctum":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="delete comment",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Comment Deleted Successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
    */
    public function destroy(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comment= $user->comments()->findOrFail($id);
        $comment->reactos()->detach();
        $comment->delete();

        return response()->json([
            'status' => 'Success',
            'message' => 'Comment Deleted Successfully'
        ],200);
    }
}

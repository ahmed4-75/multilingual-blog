<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/register",
 *     summary="Register new user",
 *     tags={"Authentication"},
 *     description="Create new user",
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"name","email","password","password_confirmation","lang"},
 *                 @OA\Property(property="name", type="string", maxLength=50,example="Ahmed Morgan"),
 *                 @OA\Property(property="email", type="string", format="email", description="email|unique:users,email", example="ahmed@example.com"),
 *                 @OA\Property(property="password", type="string", format="password", minLength=6, example="123456"),
 *                 @OA\Property(property="password_confirmation", type="string", format="password", example="123456"),
 *                 @OA\Property(property="phone", type="string", description="phone|unique:users,phone", example="+201234567890"),
 *                 @OA\Property(property="lang", type="string", enum={"ar","en","ur","sp"}, example="en"),
 *                 @OA\Property(property="favicon", type="string", format="binary", description="file|mimes:pdf,jpeg,jpg,png|max:6120", example="user_favicon.jpg")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="Success"),
 *             @OA\Property(property="message", type="string", example="The User is Created Successfully, the Mail has been sended to VerifyEmail go to http://localhost/blog/public/api/verify-email"),
 *             @OA\Property(property="data",ref="#/components/schemas/UserResource"),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error"
 *     )
 * )
 */

    public function __invoke(RegisterRequest $request)
    {
        $otp = random_int(100000,999999);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, 
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'lang' => $request->lang,
            'otp' => Hash::make($otp),
            'favicon' => 'user_favicon.jpg'
        ]);
        Mail::to($user->email)->send(new VerifyEmailMail($user->email,$otp));

        return response()->json(
            [
                'status' => 'Success',
                'message' => 'The User is Created Successfully, You have to verify the Email. Mail has been sent to VerifyEmail',
                'data' => new UserResource($user), 
            ],201);  
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VerifyEmailController extends Controller
{
/**
 * @OA\Post(
 *     path="/api/verify-email",
 *     summary="Verify user email using OTP",
 *     tags={"Authentication"},
 *     description="Verify user email before Login",
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","otp"},
 *             @OA\Property(property="email",type="string",format="email",description="email|exists:users,email",example="user@example.com"),
 *             @OA\Property(property="otp",type="array",description="6 digit OTP code",@OA\Items(type="integer", example=1),example={1,2,3,4,5,6})
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Email verified successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="Success"),
 *             @OA\Property(property="message", type="string", example="The Email is Verified Successfully, You can login")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Invalid OTP",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="Error"),
 *             @OA\Property(property="message", type="string", example="Invalid OTP")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="User not found or invalid request",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="Error"),
 *             @OA\Property(property="message", type="string", example="Invalid Request")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=409,
 *         description="Email already verified",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="Error"),
 *             @OA\Property(property="message", type="string", example="The Email is already verified")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
    public function __invoke(VerifyEmailRequest $request)
    {
        $user = User::where('email',$request->email)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Invalid Request'
            ],404);
        }
        if($user->otp == null and $user->email_verified_at != null){
            return response()->json([
                'status' => 'Error',
                'message' => 'The Email is already verified'
                ],409);
        }
        if(!Hash::check(implode('',$request->otp),$user->otp)){
            return response()->json([
                'status' => 'Error',
                'message' => 'Invalid OTP'
            ],400);
        }
        $user->update([
            'email_verified_at' => now(),
            'otp' => null
        ]);

        return response()->json(
            [
                'status' => 'Success',
                'message' => "The Email is Verified Successfully, You can login",
            ],200);
    }
}

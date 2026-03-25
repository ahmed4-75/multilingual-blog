<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Log in using a verified account by email or phone and password",
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"identification","password"},
     *             @OA\Property(property="identification",type="string",maxLength=50,description="Email or phone number",example="user@test.com or +201123456789"),
     *             @OA\Property(property="password",type="string",minLength=6,example="123456"),
     *             @OA\Property(property="remember",type="string",enum={"on","off"},example="off")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="message", type="string", example="Token authentication created Successfully"),
     *             @OA\Property(property="token", type="string", example="1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Error"),
     *             @OA\Property(property="message", type="string", example="Invalid Credentials"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="User email not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Error"),
     *             @OA\Property(property="message",type="string",example="Email is not verified. Verification code has been sent.You authenticated to the profile")
    
     *         )
     *     ),
     *     @OA\Response(
     *      response=422,
     *      description="Validation error"
     *     )
     * )
     */
    public function __invoke(LoginRequest $request)
    {
        $user = User::where('email',$request->identification)->orWhere('phone',$request->identification)->first();
        
        if($user and Hash::check($request->password,$user->password)){
            if(!$user->email_verified_at){
                $otp = random_int(100000,999999);
                $user->update(['otp' => Hash::make($otp)]);
                Mail::to($user->email)->send(new VerifyEmailMail($user->email,$otp));
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Email is not verified. Verification code has been sent.',
                ],403);
            }
            if($request->remember === 'on'){
                $token = $user->createToken('remember-authentication',['*'],Carbon::now()->addMonths(6))->plainTextToken;
            }
            else{
                $token = $user->createToken('authentication',['*'] ,Carbon::now()->addHours(2))->plainTextToken;
            }
            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Token authentication created Successfully',
                    'token' => $token
                ],200);
        }

        return response()->json(
            [
                'status' => 'Error',
                'message' => 'Invalid Credentials',
            ],401);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Enums\SocialAuthDriverEnum;
use App\Enums\SocialAuthTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/auth/{driver}/redirect/{type}",
 *     tags={"Authentication"},
 *     summary="Redirect user to social provider",
 *     description="Redirects the user to Google or GitHub authentication page",
 *
 *     @OA\Parameter(name="driver",in="path",required=true,description="Social driver",
 *         @OA\Schema(type="string",enum={"google","github"})
 *     ),
 *
 *     @OA\Parameter(name="type",in="path",required=true,description="Authentication type",
 *         @OA\Schema(type="string",enum={"login","register"})
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Redirect to social provider",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="Success"),
 *             @OA\Property(property="redirect_url", type="string", example="https://accounts.google.com/o/oauth2/auth?..."),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
    public function redirect(string $driver, string $type)
    {
        Validator::make(compact('driver','type'),[
            'driver' => ['required', new Enum(SocialAuthDriverEnum::class)],
            'type'   => ['required', new Enum(SocialAuthTypeEnum::class)],
        ])->validate();

        $callbackUrl = url("/api/auth/$driver/callback?type=$type");
        /** @var \Laravel\Socialite\Two\AbstractProvider $provider */
        /** @var \Laravel\Socialite\Two\AbstractProvider $provider */
        $provider =Socialite::driver($driver);
        $redirectUrl = $provider->stateless()->redirectUrl($callbackUrl)->redirect()->getTargetUrl();
        
        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl
        ], 200);
    }
    
/**
 * @OA\Get(
 *     path="/api/auth/{driver}/callback",
 *     tags={"Authentication"},
 *     summary="Social provider callback",
 *     description="Handles callback from Google or GitHub for authentication",
 *
 *     @OA\Parameter(name="driver",in="path",required=true,description="Social driver",
 *         @OA\Schema(type="string",enum={"google","github"})
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
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="Success"),
 *             @OA\Property(property="message", type="string", example="The User is Created Successfully and has been Verified"),
 *             @OA\Property(property="data",ref="#/components/schemas/UserResource"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Authentication failed"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
    public function callback(string $driver)
    {
        $type = request('type');
        Validator::make(compact('driver','type'),
            [
            'driver' => ['required', new Enum(SocialAuthDriverEnum::class)],
            'type' => ['required', new Enum(SocialAuthTypeEnum::class)]
            ]
        )->validate();

        try{
            /** @var \Laravel\Socialite\Two\AbstractProvider $provider */
            $provider = Socialite::driver($driver);
            $socialUser = $provider->stateless()->user();
        }catch(\Exception $e){
            return response()->json([
                'status' => 'Error',
                'message' => 'Authentication failed'
            ],400);
        }
        $userData = [
            'name'  => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
        ];

        Validator::make(
            $userData,
            [
                'name' => 'required|string|max:50',
                'email' => 'required|email'
            ]
        )->validate();

        switch($type){
        case SocialAuthTypeEnum::LOGIN->value:
            $user = User::where('email',$userData['email'])->first();
            if($user){
                if (!$user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }
                $token = $user->createToken('authentication')->plainTextToken;
                return response()->json([
                    'status' => 'success',
                    'message' => 'Token authentication created Successfully',
                    'token' => $token
                ],200);
            }
            return response()->json([
                'status' => 'Error',
                'message' => 'Invalid Credentials',
            ],401);
        
            case SocialAuthTypeEnum::REGISTER->value:
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)),
                    'otp' => null
                ]
            );
            return response()->json([
                'status' => 'Success',
                'message' => 'The User is Created Successfully and has been Verified',
                'data' => new UserResource($user), 
            ],201);
        }
    }
}
<?php

use App\Enums\PermissionsEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LoginNoPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlogContentController;
use App\Http\Controllers\ReactosController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/api', function () { return view('welcome'); });

Route::post('/register',RegisterController::class);
Route::post('/verify-email',VerifyEmailController::class);
Route::post('/login',LoginController::class);
Route::post('/forgot-password',[LoginNoPasswordController::class,'forgotPassword'],);
Route::post('/reset-password',[LoginNoPasswordController::class,'resetPassword'],);
Route::get('/auth/{driver}/redirect/{type}',[SocialAuthController::class,'redirect']);
Route::get('/auth/{driver}/callback',[SocialAuthController::class,'callback']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/profile',[ProfileController::class,'index']);  
    Route::post('/profile/update',[ProfileController::class,'update']);
    Route::put('/profile/update-password',[ProfileController::class,'updatePassword']);
    Route::post('/logout',[ProfileController::class,'logout']);

    Route::apiResource('/posts',PostsController::class);
    Route::delete('/posts/private/{post}',[PostsController::class,'PrivatePost']);
    Route::get('/posts/public/{post}',[PostsController::class,'publicPost']);

    Route::apiResource('/comments',CommentsController::class);

    Route::middleware(['permission:'. PermissionsEnum::SELECT_REACT->value])->group(function(){
        Route::post('/reactos/post',[ReactosController::class,'reactPost']);
        Route::post('/reactos/comment',[ReactosController::class,'reactComment']);
    });
    Route::get('/blog-content',[BlogContentController::class,'index'])->middleware('permission:'. PermissionsEnum::VIEW_BLOG_CONTENT->value);
    Route::get('/blog-content/search/{user_id}',[BlogContentController::class,'getUserPosts']);

    Route::get('/users',[UsersController::class,'index'])->middleware('permission:'. PermissionsEnum::VIEW_USERS->value);
    Route::put('/users/change-role/{user}',[UsersController::class,'changeRole'])->middleware('permission:'. PermissionsEnum::CHANGE_USER_ROLES->value .'|'. PermissionsEnum::CHANGE_VIU_ROLES->value);
    Route::delete('/users/ban/{id}',[UsersController::class,'banUser'])->middleware('permission:'. PermissionsEnum::BAN_USER->value);
    Route::get('/users/activate/{id}',[UsersController::class,'activateUser'])->middleware('permission:'. PermissionsEnum::ACTIVATE_USER->value);
    Route::delete('/users/delete/{id}',[UsersController::class,'destroyUser'])->middleware('permission:'. PermissionsEnum::DESTROY_USER->value);

    Route::apiResource('roles',RolesController::class);
});

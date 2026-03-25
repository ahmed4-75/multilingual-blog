<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-email/{email}',function(){ return"Verify Email Page"; });
Route::get('/login',function(){ return"Login Page"; });
Route::get('/forgot-password',function(){ return"Forgot Password Page";});
Route::get('/reset-password/{email}',function(){ return"Reset Password Page";});

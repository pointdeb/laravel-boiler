<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset')->middleware(['guest'])->where('token', '[\w\d]+');
Route::get('/auth/email/confirm/{token}', 'Auth\RegisterController@confirmEmail')->name('auth.email.confirm')->middleware(['guest'])->where('token', '[\w\d]+');

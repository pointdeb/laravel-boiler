<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return \App\User::withRules()->findOrFail($request->user()->user_id);
});

# oauth 2.0
Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken')->name('passport.token')->middleware(['guest']);
Route::group(['prefix' => 'oauth','namespace' => '\Laravel\Passport\Http\Controllers', 'middleware' => 'auth:api', 'as' => 'passport.'], function () {
    Route::post('/authorize', 'ApproveAuthorizationController@approve')->name('authorizations.approve');
    Route::get('/authorize', 'AuthorizationController@authorize')->name('authorizations.authorize');
    Route::delete('/tokens', function (Request $request) {
        $request->user()->tokens()->each(function ($item) {
            $item->revoke();
        });
        abort(204);
    })->name('tokens.destroy.user');
    Route::post('/token/refresh', 'TransientTokenController@refresh')->name('token.refresh');
    Route::delete('/tokens/{token_id}', 'AuthorizedAccessTokenController@destroy')->name('tokens.destroy');
});

Route::group(['prefix' => 'auth', 'as' => 'auth.', 'namespace' => '\App\Http\Controllers\Auth'], function () {
    Route::post('register', 'RegisterController@register')
        ->name('register')
        ->middleware(['guest']);

    Route::apiResource('/rules', 'RuleController')
        ->parameters(['rules' => 'rule_id'])
        ->names(['rules' => 'rules.'])
        ->middleware(['auth:api', 'is_admin']);

    Route::post('/password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email')->middleware(['guest']);
    Route::put('/password/update', 'ResetPasswordController@reset')->name('password.reset')->middleware(['guest']);
});


Route::group(['prefix' => 'locale', 'as' => 'locale.', 'namespace' => '\App\Http\Controllers\Locale'], function () {
    Route::post('from-file', 'LocaleController@fromFile')
        ->name('from-file')
        ->middleware(['auth:api', 'is_admin']);
    Route::get('translate/{key}/{locale}', 'LocaleController@translate')
        ->name('translate')->where('key', '[\w\.]+');
});

if (env('APP_ENV') == 'local') {
    Route::group(['prefix' => 'config', 'as' => 'config.'], function () {
        Route::get('/credentials/{id?}', function (Request $request, int $id = 2) {
        return json_encode(DB::SELECT("SELECT id as client_id, secret as client_secret FROM oauth_clients WHERE id = {$id}")[0]);
        })->name('credentials');
    });
}



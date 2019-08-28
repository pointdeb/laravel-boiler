<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Rule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Notifications\Auth\RegisteredNotification;

class RegisterController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        User::validator($request->input())->validate();
        $confirmation_token = Str::random(40);
        $user = User::create(array_merge($request->input(), ['confirmation_token' => bcrypt($confirmation_token)]));
        $rule = Rule::where('alias', 'default')->select('rule_id')->first();
        $user->rules()->sync([$rule->rule_id]);
        $user->save();
        event(new Registered($user));
        $user->notify(new RegisteredNotification($confirmation_token));
        return Response::json($user, 201);
    }

    public function confirmEmail(Request $request, string $token)
    {
        $user = User::where(['email' => $request->input('email')])->firstOrFail();
        if (!Hash::check($token, $user->confirmation_token)) {
            abort(405);
        }
        $user->update(['email_verified_at' => now(), 'confirmation_token' => null]);
        $user->save();
        return redirect('/');
    }
}

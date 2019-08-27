<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Rule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class RegisterController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        User::validator($request->input())->validate();
        $user = User::create($request->input());
        $rule = Rule::where('alias', 'default')->select('rule_id')->first();
        $user->rules()->sync([$rule->rule_id]);
        $user->save();
        return Response::json($user, 201);
    }
}

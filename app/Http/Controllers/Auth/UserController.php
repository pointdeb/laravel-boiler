<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
      if (count(Input::all()) == 0) {
          return User::withRules()->paginate($request->input('per_page') ?? 10)->withPath('');
      }

      $query = null;
      $this->ignoreSearchFields = array_merge($this->ignoreSearchFields, []);

      foreach($request->input() as $key => $value) {
          if (in_array($key, $this->ignoreSearchFields)) {
            continue;
          }
          if (!$query) {
              $query = User::withRules()->where($key, 'like', '%'. $value .'%');
              continue;
          }
          $query = $query->orWhere($key, 'like', '%'. $value .'%');
      }

      if ($query == null) {
        $users = User::withRules()->paginate($request->input('per_page') ?? 10)->withPath('');
      } else {
        $users = $query->paginate($request->input('per_page') ?? 10)->withPath('');
      }
      return response($users);
    }

    public function show(Request $request, int $user_id)
    {
        return User::withUsers()->findOrFail($user_id);
    }

    public function store(Request $request)
    {
        User::validator($request->input())->validate();
        $user = User::create($request->input());
        return response($user, 201);
    }

    public function update(Request $request, int $user_id)
    {
        abort_if($request->user()->is_admin == false && $request->user()->user_id != $user_id, 403);
        User::validator($request->input(), true)->validate();
        $user = User::findOrFail($user_id);
        $user->update($request->input());
        $user->save();
        return response($user, 200);
    }

    public function updateRules(Request $request)
    {
        Validator::make($request->input(), [
            'users' => ['required', 'tokenfield'],
            'rules' => ['required', 'tokenfield'],
        ])->validate();
        $rules = explode(',', $request->input('rules'));
        $users = User::whereIn('user_id', explode(',', $request->input('users')))->get()->each(function ($item) use ($rules) {
            $item->rules()->sync($rules);
            $item->save();
        });
        abort(204);
    }

    public function destroy(Request $request, int $user_id)
    {
        $user = User::findOrFail($user_id);
        if ($user->is_admin) {
            abort(405);
        }
        $user->delete();
        return response($user, 200);
    }

}

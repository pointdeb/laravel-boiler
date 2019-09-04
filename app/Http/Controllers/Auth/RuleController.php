<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Rule;

class RuleController extends Controller
{
    public function index(Request $request)
    {
      if (count(Input::all()) == 0) {
          return Rule::paginate($request->input('per_page') ?? 10)->withPath('');
      }

      $query = null;
      $this->ignoreSearchFields = array_merge($this->ignoreSearchFields, []);

      foreach($request->input() as $key => $value) {
          if (in_array($key, $this->ignoreSearchFields)) {
            continue;
          }
          if (!$query) {
              $query = Rule::where($key, 'like', '%'. $value .'%');
              continue;
          }
          $query = $query->orWhere($key, 'like', '%'. $value .'%');
      }

      if ($query == null) {
        $rules = Rule::paginate($request->input('per_page') ?? 10)->withPath('');
      } else {
        $rules = $query->paginate($request->input('per_page') ?? 10)->withPath('');
      }
      return response($rules);
    }

    public function show(Request $request, int $rule_id)
    {
        return Rule::withUsers()->findOrFail($rule_id);
    }

    public function store(Request $request)
    {
        Rule::validator($request->input())->validate();
        $rule = Rule::create($request->input());
        return response($rule, 201);
    }

    public function update(Request $request, int $rule_id)
    {
        Rule::validator($request->input())->validate();
        $rule = Rule::findOrFail($rule_id);
        $rule->update($request->input());
        $rule->save();
        return response($rule, 200);
    }

    public function destroy(Request $request, int $rule_id)
    {
        $rule = Rule::findOrFail($rule_id);
        if (in_array($rule->alias, Rule::$requiredAliases)) {
            abort(405);
        }
        $rule->delete();
        return response($rule, 200);
    }

}

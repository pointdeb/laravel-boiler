<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Rule;

class RuleController extends Controller
{
    public function index()
    {
        return Rule::simplePaginate()->withPath('');
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
        return response($rule, 410);
    }

}

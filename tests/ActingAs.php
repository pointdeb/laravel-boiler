<?php
namespace Tests;

use App\User;
use App\Rule;
use Laravel\Passport\Passport;

trait ActingAs
{
    public $user = null;

    /**
     * Creating fake user for the requested test
     * @param bool $isAdmin
     * @return User|null
     */
    public function getActingAs(bool $isAdmin = false): User
    {
        $this->user = $this->user ?? factory(User::class)->create();
        if ($isAdmin) {
            $adminRules = Rule::where('alias', 'like', '%admin%')->select('rule_id')->get()->map(function ($item) {
                return $item->rule_id;
            });
            $this->user->rules()->sync($adminRules);
            $this->user->save();
        }
        Passport::actingAs($this->user);
        return $this->user;
    }
}

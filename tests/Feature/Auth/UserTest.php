<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\User;
use App\Rule;
use Tests\SeedDatabase;
use Tests\ActingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase, SeedDatabase, ActingAs;

    public function testUserGetAll()
    {
        $currentCount = User::count();
        factory(User::class, 120)->create();
        $this->getActingAs(true);
        $response = $this->json('GET', route('auth.users.index'));
        $this->assertEquals(200, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent());
        $this->assertEquals(10, count($responseObj->data));
        $this->assertNotContains('http', $responseObj->first_page_url);
    }

    public function testUserUpdate()
    {
        $this->getActingAs(true);
        $user = factory(User::class)->create()->toArray();
        $user['name'] .= '_modified';
        $response = $this->json('PUT', route('auth.users.update', ['user_id' => $user['user_id']]), $user);
        $this->assertEquals(200, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent(), true);
        $this->assertEquals($user['name'], $responseObj['name']);
    }

    public function testUserUpdateRules()
    {
        $this->getActingAs(true);
        $users = factory(User::class, 10)->create();
        $usersData = $users->map(function ($item) {
            return $item->user_id;
        })->toArray();
        $rules = factory(Rule::class, 10)->create();
        $rulesData = $rules->map(function ($item) {
            return $item->rule_id;
        })->toArray();
        $data = ['users' => implode(',', $usersData), 'rules' => implode(',', $rulesData)];
        $response = $this->json('PUT', route('auth.users.rules.update'), $data);
        $this->assertEquals(204, $response->status(), $response->getContent());
    }

    public function testUserUpdateRulesByLeavingLastAdmin()
    {
        $authUser = $this->getActingAs(true);
        $this->assertEquals($authUser->is_admin, true);
        $user = factory(User::class)->create();
        $adminRules = Rule::where('alias', 'like', '%admin%')->select('rule_id')->get()->map(function ($item) {
            return $item->rule_id;
        });
        $user->rules()->sync($adminRules);
        $rules = factory(Rule::class, 10)->create();
        $rulesData = $rules->map(function ($item) {
            return $item->rule_id;
        })->toArray();
        $data = ['users' => implode(',', [$user->user_id, $authUser->user_id]), 'rules' => implode(',', $rulesData)];
        $response = $this->json('PUT', route('auth.users.rules.update'), $data);
        $this->assertEquals(204, $response->status(), $response->getContent());
        $this->assertEquals($authUser->is_admin, true);
    }

    // public function testUserUpdateRulesNonAdmin()
    // {
    //     $authUser = $this->getActingAs();
    //     $user = factory(User::class)->create()->toArray();
    //     $user['name'] .= '_modified';
    //     // update others
    //     $response = $this->json('PUT', route('auth.users.update', ['user_id' => $user['user_id']]), $user);
    //     $this->assertEquals(403, $response->status(), $response->getContent());
    //     // update self
    //     $authUser->name = $authUser->name . '_modified';
    //     $response = $this->json('PUT', route('auth.users.update', ['user_id' => $authUser->user_id]), $authUser->toArray());
    //     $this->assertEquals(403, $response->status(), $response->getContent());
    //     $responseObj = json_decode($response->getContent(), true);
    //     $this->assertEquals($authUser->name, $responseObj['name']);
    // }

    public function testUserDelete()
    {
        $this->getActingAs(true);
        $user = factory(User::class)->create();
        $response = $this->json('DELETE', route('auth.users.destroy', ['user_id' => $user['user_id']]));
        $this->assertEquals(200, $response->status(), $response->getContent());
        $this->assertNull(User::find($user->user_id));
    }
}

<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\User;
use App\Rule;
use Tests\SeedDatabase;
use Tests\ActingAs;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Support\Facades\Route;

class RuleTest extends TestCase
{
    use RefreshDatabase, SeedDatabase, ActingAs;

    public function testRuleIsAdmin()
    {
        $user = factory(User::class)->create();
        $adminRules = Rule::where('alias', 'like', '%admin%')->select('rule_id')->get()->map(function ($item) {
            return $item->rule_id;
        });
        $user->rules()->sync($adminRules);
        $user->save();
        $this->assertTrue($user->is_admin, $user->rules);
    }

    public function testRuleIsAdminMiddlware()
    {
        Route::get('/tests/admin', [
            'as' => 'tests.admin',
            'uses' => function () {
                return ['message' => 'admin content'];
            },
            'middleware' => ['auth:api', 'is_admin']
        ]);
        $this->getActingAs(true);
        $response = $this->json('GET', route('tests.admin'));
        $this->assertEquals(200, $response->status(), $response->getContent());
        $message = json_decode($response->getContent())->message;
        $this->assertEquals('admin content', $message, $response->getContent());
    }

    public function testRuleAdd()
    {
        $this->getActingAs(true);
        $rule = factory(Rule::class)->make()->toArray();
        $response = $this->json('POST', route('auth.rules.store'), $rule);
        $this->assertEquals(201, $response->status(), $response->getContent());
        $rule = json_decode($response->getContent());
        $this->assertTrue($rule->rule_id > 0);
    }

    public function testRuleAddWithMissingFieldValidationErrors()
    {
        $this->getActingAs(true);
        $rule = ['label' => 'my_rule'];
        $response = $this->json('POST', route('auth.rules.store'), $rule);
        $this->assertEquals(422, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('alias', $responseObj['errors']);
        $this->assertContains('The alias field is required.', $responseObj['errors']['alias'][0]);
    }

    public function testRuleAddWithDuplicateValidationErrors()
    {
        $this->getActingAs(true);
        $rule = factory(Rule::class)->create()->toArray();
        unset($rule['rule_id']);
        $response = $this->json('POST', route('auth.rules.store'), $rule);
        $this->assertEquals(422, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('label', $responseObj['errors']);
        $this->assertContains('The label has already been taken', $responseObj['errors']['label'][0]);
    }

    public function testRuleUpdate()
    {
        $this->getActingAs(true);
        $rule = factory(Rule::class)->create()->toArray();
        $rule['label'] .= '_modified';
        $response = $this->json('PUT', route('auth.rules.update', ['rule_id' => $rule['rule_id']]), $rule);
        $this->assertEquals(200, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent(), true);
        $this->assertEquals($rule['label'], $responseObj['label']);
    }

    public function testRuleUpdateWithDuplicateValidationErrors()
    {
        $this->getActingAs(true);
        $rules = factory(Rule::class, 2)->create()->toArray();
        $rule = $rules[0];
        $rule2 = $rules[1];
        $rule['label'] = $rule2['label'];
        $response = $this->json('PUT', route('auth.rules.update', ['rule_id' => $rule['rule_id']]), $rule);
        $this->assertEquals(422, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('label', $responseObj['errors']);
        $this->assertContains('The label has already been taken', $responseObj['errors']['label'][0]);
    }

    public function testRuleDelete()
    {
        $this->getActingAs(true);
        $rule = factory(Rule::class)->create();
        $response = $this->json('DELETE', route('auth.rules.destroy', ['rule_id' => $rule['rule_id']]));
        $this->assertEquals(410, $response->status(), $response->getContent());
        $this->assertNull(Rule::find($rule->rule_id));
    }

    public function testRuleDeleteByLeavingRequired()
    {
        $this->getActingAs(true);
        $requiredAliases = Rule::$requiredAliases;
        factory(Rule::class, 2)->create();
        $rules = Rule::all();
        $max = count($rules) - 1;
        for ($i=$max; $i > 0; $i--) {
            $rule = $rules[$i];
            $response = $this->json('DELETE', route('auth.rules.destroy', ['rule_id' => $rule->rule_id]));
            if (in_array($rule->alias, $requiredAliases)) {
                $this->assertEquals(405, $response->status(), $response->getContent());
                $this->assertNotNull(Rule::find($rule->rule_id));
            } else {
                $this->assertEquals(410, $response->status(), $response->getContent());
                $this->assertNull(Rule::find($rule->rule_id));
            }
        }
        $this->assertEquals(count($requiredAliases), Rule::count());
    }

    public function testRuleGetAll()
    {
        $currentCount = Rule::count();
        factory(Rule::class, 120)->create();
        $this->getActingAs(true);
        $response = $this->json('GET', route('auth.rules.index'));
        $this->assertEquals(200, $response->status(), $response->getContent());
        $responseObj = json_decode($response->getContent());
        $this->assertEquals(10, count($responseObj->data));
        $this->assertNotContains('http', $responseObj->first_page_url);
    }
}

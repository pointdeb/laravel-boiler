<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Tests\SeedDatabase;
use Tests\ActingAs;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;

class OauthTest extends TestCase
{
    use RefreshDatabase, SeedDatabase, ActingAs;

    public function testOauthCreateClient()
    {
        $this->getActingAs(true);
        $data = factory(Client::class)->make()->toArray();
        $response = $this->json('POST', route('passport.clients.store'), $data);
        $this->assertEquals(201, $response->status(), $response->getContent());
    }

    public function testOauthUpdateClient()
    {
        $user = $this->getActingAs(true);
        $client = factory(Client::class)->create(['user_id' => $user->user_id])->toArray();
        $response = $this->json('PUT', route('passport.clients.update', $client['id']), $client);
        $this->assertEquals(200, $response->status(), $response->getContent());
    }

    public function testOauthDeleteClient()
    {
        $user = $this->getActingAs(true);
        $client = factory(Client::class)->create(['user_id' => $user->user_id])->toArray();
        $this->assertDatabaseHas('oauth_clients', [
            'id' => $client['id'],
            'revoked' => 0,
        ]);

        $response = $this->json('DELETE', route('passport.clients.destroy', $client['id']));
        $this->assertEquals(204, $response->status(), $response->getContent());
        $this->assertDatabaseMissing('oauth_clients', [
            'id' => $client['id'],
            'revoked' => 0,
        ]);
    }

    public function testOauthScopeAll()
    {
        $user = $this->getActingAs(true);
        $response = $this->json('GET', route('passport.scopes.index'));
        $this->assertEquals(200, $response->status(), $response->getContent());
        $responseContent = json_decode($response->getContent());
        $this->assertEquals(2, count($responseContent));
    }
}

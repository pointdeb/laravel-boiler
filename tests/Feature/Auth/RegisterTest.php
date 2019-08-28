<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;
use Tests\SeedDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spinen\MailAssertions\MailTracking;

class RegisterTest extends TestCase
{
    use RefreshDatabase, SeedDatabase, MailTracking;

    public function testRegisterWithEmailConfirmation()
    {
        $data = ['name' => 'john', 'email' => 'john@mail.com', 'password' => 'password', 'password_confirmation' => 'password'];
        $response = $this->json('POST', route('auth.register'), $data);
        $response->assertStatus(201, $response);
        $content = json_decode($response->getContent());
        $user = User::find($content->user_id);
        $this->assertEquals(1, $user->rules->count(), $user->rules);
        $this->seeEmailWasSent();

        preg_match("#auth/email/confirm/([\w\d\=\n]+)\?#", $this->lastEmail()->getBody(), $matches);
        $token = $matches[1];

        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
            'confirmation_token' => null,
        ]);

        $response = $this->json('GET', route('auth.email.confirm', ['token' => $token]), $data);
        $response->assertStatus(302, $response);

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'confirmation_token' => null,
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => $user->email,
            'email_verified_at' => null,
        ]);

        $data = ['name' => 'john', 'email' => 'john@mail.com', 'password' => 'password', 'password_confirmation' => 'passwords'];
        $response = $this->json('POST', route('auth.register'), $data);
        $this->assertEquals(422, $response->status(), $response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('email', $content['errors'], $response->getContent());
        $this->assertContains('already', $content['errors']['email'][0], $response->getContent());
        $this->assertArrayHasKey('password', $content['errors'], $response->getContent());
        $this->assertContains('does not match', $content['errors']['password'][0], $response->getContent());
    }

}

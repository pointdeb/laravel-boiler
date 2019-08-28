<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;
use Tests\SeedDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spinen\MailAssertions\MailTracking;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase, SeedDatabase, MailTracking;

    public function testResetPasswordWithEmailConfirmation()
    {
        $user = factory(User::class)->create(['email' => 'john@mail.com']);
        $data = ['email' => 'john@mail.com'];
        $response = $this->json('POST', route('auth.password.email'), $data);
        $this->assertEquals(201, $response->status(), $response->getContent());
        $content = json_decode($response->getContent());

        $this->assertDatabaseHas('password_resets', [
            'email' => $user->email
        ]);

        $this->seeEmailWasSent();
        preg_match("#auth/password/reset/([\w\d\=\n]+)\?#", $this->lastEmail()->getBody(), $matches);
        $token = $matches[1];
        $response = $this->json('PUT', route('auth.password.reset'), ['token' => $token, 'email' => $user->email, 'password' => 'mynewlongpassword', 'password_confirmation' => 'mynewlongpassword']);
        $this->assertEquals(201, $response->status(), $response->getContent());
        $this->assertDatabaseMissing('password_resets', [
            'email' => $user->email
        ]);
    }
}

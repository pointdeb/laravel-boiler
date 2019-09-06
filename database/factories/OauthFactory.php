<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Laravel\Passport\Client;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Client::class, function (Faker $faker) {
    $name = $faker->unique()->firstName();
    return [
        "name" => $name,
        "secret" => Str::random(40),
        "redirect" => "http://{$name}.com",
        "personal_access_client" => false,
        "password_client" => false,
        "revoked" => false,
        "user_id" => rand(1, 5)
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Rule;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Rule::class, function (Faker $faker) {
    $name = $faker->unique()->name;
    return [
        'label' => $name,
        'alias' => Str::slug($name, '_')
    ];
});

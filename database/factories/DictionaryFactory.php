<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Dictionary;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Dictionary::class, function (Faker $faker) {
    $value = $faker->unique()->name();
    return [
        'key' => Str::slug($value, '.'),
        'value' => $value,
        'locale' => $faker->languageCode
    ];
});

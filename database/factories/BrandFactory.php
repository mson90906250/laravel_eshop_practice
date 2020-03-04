<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Brand;
use Faker\Generator as Faker;

$factory->define(Brand::class, function (Faker $faker) {
    return [
        'name' => sprintf('Brand %s', $faker->unique()->text(20))
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {

    $category = $faker->boolean(75) ? Category::inRandomOrder()->first() : NULL;

    return [
        'parent_id' => ($category && !$category->parent_id) ? $category->id : NULL,
        'category' => $faker->unique()->text(20)
    ];
});

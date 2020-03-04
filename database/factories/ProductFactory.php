<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Brand;
use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name'          => sprintf('Product %s', $faker->word),
        'brand_id'      => Brand::inRandomOrder()->first()->id,
        'original_price' => $faker->boolean(50) ? rand(1, 1000) : NULL,
        'description'   => $faker->paragraph(10),
    ];
});

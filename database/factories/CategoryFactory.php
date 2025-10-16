<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;


class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'name' => fake()->word(),
            'created_at' => fake()->date('Y-m-d H:i:s'),
            'updated_at' => fake()->date('Y-m-d H:i:s')
        ];
    }
}

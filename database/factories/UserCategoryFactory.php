<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Database\Eloquent\Factories\Factory;


class UserCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomElement(User::pluck('id')),
            'category_id' => $this->faker->randomElement(Category::pluck('id')),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

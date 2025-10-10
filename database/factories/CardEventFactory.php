<?php

namespace Database\Factories;

use App\Models\CardEvent;
use Illuminate\Database\Eloquent\Factories\Factory;


class CardEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CardEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'type' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'meta_json' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

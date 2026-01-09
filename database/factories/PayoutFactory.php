<?php

namespace Database\Factories;

use App\Models\Payout;
use Illuminate\Database\Eloquent\Factories\Factory;


class PayoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payout::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'gross_amount' => $this->faker->numberBetween(0, 999),
            'net_amount' => $this->faker->numberBetween(0, 999),
            'fees' => $this->faker->numberBetween(0, 999),
            'currency' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'status' => $this->faker->word,
            'partner_id' => $this->faker->numberBetween(0, 999),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

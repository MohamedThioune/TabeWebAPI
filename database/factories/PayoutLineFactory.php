<?php

namespace Database\Factories;

use App\Models\PayoutLine;
use Illuminate\Database\Eloquent\Factories\Factory;


class PayoutLineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PayoutLine::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'amount' => $this->faker->numberBetween(0, 999),
            'transaction_id' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'payout_id' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\GiftCard;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'id' => $this->faker->uuid(),
            'status' => $this->faker->randomElement(['authorized', 'captured', 'cancelled', 'refunded', 'failed']),
            'amount' => $this->faker->numberBetween(config('parameter.card.min_amount'), config('parameter.card.max_amount')),
            'currency' => 'FCFA',
            'user_id' => $this->faker->randomElement(User::pluck('id')),
            'gift_card_id' => $this->faker->randomElement(GiftCard::pluck('id')),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\GiftCard;

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
            'status' => $this->faker->randomElement(['authorized', 'completed', 'cancelled', 'failed']),
            'amount' => $this->faker->numberBetween(10000, 150000),
            'currency' => 'FCFA',
            'user_id' => $this->faker->randomElement(User::pluck('id')),
            'gift_card_id' => $this->faker->randomElement(GiftCard::pluck('id')),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

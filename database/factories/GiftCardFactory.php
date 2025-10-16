<?php

namespace Database\Factories;

use App\Models\Beneficiary;
use App\Models\Category;
use App\Models\Design;
use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class GiftCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GiftCard::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'belonging_type' => $this->faker->randomElement(['myself', 'others']),
            'pin_hash' => bcrypt($this->faker->numberBetween(10, 99)),
            'pin_mask' => $this->faker->numberBetween(10, 99),
            'face_amount' => $this->faker->numberBetween(10000, 150000),
            'expired_at' => $this->faker->dateTimeBetween('-1 week', '+3 weeks'),
            'owner_user_id' => $this->faker->randomElement(User::pluck('id')),
            'beneficiary_id' => $this->faker->randomElement(Beneficiary::pluck('id')),
            'design_id' => $this->faker->randomElement(Design::pluck('id')),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Beneficiary;
use App\Models\Category;
use App\Models\Design;
use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\Users\ValueObjects\Type;

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
            'code' => $this->faker->regexify('#[A-Z]{4}-\d{4}-[A-Z]#'),
            'status' => "active",
            'type' => $this->faker->randomElement(['physical', 'digital']),  
            'belonging_type' => $this->faker->randomElement(['myself', 'others']),
            'face_amount' => $this->faker->numberBetween(10000, 150000),
            'expired_at' => $this->faker->dateTimeBetween('+3 week', '+3 months'),
            'issued_via' => $this->faker->randomElement(['B2C', 'B2B', 'Admin']),
            'owner_user_id' => $this->faker->randomElement(User::role(Type::Customer->value)->pluck('id')),
            'beneficiary_id' => $this->faker->randomElement(Beneficiary::pluck('id')),
            'design_id' => $this->faker->randomElement(Design::pluck('id')),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\QrSession;
use Illuminate\Database\Eloquent\Factories\Factory;


class QRSessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QrSession::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'token' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'url' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'expired_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

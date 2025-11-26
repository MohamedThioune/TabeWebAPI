<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;


class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'reference_number' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'token' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'status' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'endpoint' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'user_id' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

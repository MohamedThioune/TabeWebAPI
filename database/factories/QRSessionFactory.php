<?php

namespace Database\Factories;

use App\Models\QrSession;
use App\Models\GiftCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\GiftCards\UseCases\CardFullyGenerated;



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
        $qr_id = $this->faker->uuid();
        $qr = CardFullyGenerated::qr_url($qr_id);
        return [
            'id' => $qr_id,
            'status' => 'pending',
            'token' => $qr['payload'] ?? null,
            'url' => $qr['url'] ?? null,
            'gift_card_id' => $this->faker->randomElement(GiftCard::pluck('id')),
            'expired_at' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}

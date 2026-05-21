<?php

namespace Database\Seeders;

use App\Models\GiftCard;
use Illuminate\Database\Seeder;

class GiftCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GiftCard::factory(5)->create();
    }
}

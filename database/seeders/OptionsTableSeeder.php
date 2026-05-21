<?php

namespace Database\Seeders;

use App\Models\Option;
use Illuminate\Database\Seeder;

class OptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Option::firstOrCreate([
            'min_amount_card' => config('parameter.card.min_amount'),
            'max_amount_card' => config('parameter.card.max_amount'),
            'period_validity_card' => config('parameter.card.period_validity'),
        ]);

    }
}

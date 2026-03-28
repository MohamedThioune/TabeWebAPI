<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;

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
            'period_validity_card' => config('parameter.card.period_validity')
        ]);

    }
}

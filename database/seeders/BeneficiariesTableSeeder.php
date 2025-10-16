<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BeneficiariesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() : void
    {
         \App\Models\Beneficiary::factory(10)->create();
    }
}

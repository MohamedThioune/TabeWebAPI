<?php

namespace Database\Seeders;

use App\Models\Beneficiary;
use Illuminate\Database\Seeder;

class BeneficiariesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Beneficiary::factory(10)->create();
    }
}

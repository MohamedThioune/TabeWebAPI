<?php

namespace Database\Seeders;

use App\Domain\Users\ValueObjects\Type;
use App\Models\Design;
use Illuminate\Database\Seeder;

class DesignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Design::firstOrCreate(['name' => 'Standard']);
        Design::firstOrCreate(['name' => 'Elegant']);
        Design::firstOrCreate(['name' => 'Premium']);
        Design::firstOrCreate(['name' => 'Elite']);
    }
}

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
        Design::create(['name' => 'Standard']);
        Design::create(['name' => 'Elegant']);
        Design::create(['name' => 'Premium']);
        Design::create(['name' => 'Elite']);
    }
}

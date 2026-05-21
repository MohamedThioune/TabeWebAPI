<?php

namespace Database\Seeders;

use App\Models\Design;
use Illuminate\Database\Seeder;

class DesignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Design::firstOrCreate(['name' => 'Classic']);
        Design::firstOrCreate(['name' => 'Moderne']);
        Design::firstOrCreate(['name' => 'Elegant']);
        Design::firstOrCreate(['name' => 'Premium']);
    }
}

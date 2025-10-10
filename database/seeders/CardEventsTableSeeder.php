<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CardEventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\CardEvent::factory(20)->create();
    }
}

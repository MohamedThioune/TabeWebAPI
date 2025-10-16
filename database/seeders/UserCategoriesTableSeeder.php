<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\UserCategory::factory(5)->create();
    }
}

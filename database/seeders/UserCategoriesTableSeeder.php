<?php

namespace Database\Seeders;

use App\Models\UserCategory;
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
        UserCategory::factory(5)->create();
    }
}

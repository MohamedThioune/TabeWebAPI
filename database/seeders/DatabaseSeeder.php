<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            //Seeds the roles, partners, customers, designs, categories, user_categories
            RoleSeeder::class,
            PartnerSeeder::class,
            CustomerSeeder::class,
            DesignsTableSeeder::class,
            CategoriesTableSeeder::class,
            // UserCategoriesTableSeeder::class,
            OptionsTableSeeder::class,
        ]);
    }
}

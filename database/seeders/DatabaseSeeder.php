<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seeds the roles, partners, customers, designs, categories, user_categories
            RoleSeeder::class,
            PartnerSeeder::class,
            CustomerSeeder::class,
            DesignsTableSeeder::class,
            CategoriesTableSeeder::class,
            BeneficiariesTableSeeder::class,
            GiftCardSeeder::class,
            // UserCategoriesTableSeeder::class,
            OptionsTableSeeder::class,
        ]);

        // Create a admin user
        $user = User::factory()->create();
        $user->assignRole('admin');

    }
}

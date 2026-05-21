<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::firstOrCreate(['name' => 'Vêtements']);
        Category::firstOrCreate(['name' => 'Accessoires']);
        Category::firstOrCreate(['name' => 'Chaussures']);
        Category::firstOrCreate(['name' => 'Maroquinerie']);
        Category::firstOrCreate(['name' => 'Spa']);
        Category::firstOrCreate(['name' => 'Cosmétiques']);
        Category::firstOrCreate(['name' => 'Parfumerie']);
        Category::firstOrCreate(['name' => 'Cuisine francaise']);
        Category::firstOrCreate(['name' => 'Menu dégustation']);
        Category::firstOrCreate(['name' => 'Vins fins']);
        Category::firstOrCreate(['name' => 'Desserts']);
        Category::firstOrCreate(['name' => 'Smartphones']);
        Category::firstOrCreate(['name' => 'Ordinateurs']);
        Category::firstOrCreate(['name' => 'Gaming']);
        Category::firstOrCreate(['name' => 'Accessoires']);
        Category::firstOrCreate(['name' => 'Massages']);
        Category::firstOrCreate(['name' => 'Yoga']);
        Category::firstOrCreate(['name' => 'Méditation']);
        Category::firstOrCreate(['name' => 'Aromathérapie']);
        Category::firstOrCreate(['name' => 'Mobilier']);
        Category::firstOrCreate(['name' => 'Luminaires']);
        Category::firstOrCreate(['name' => 'Textiles']);
        Category::firstOrCreate(['name' => 'Arts de la table']);
    }
}

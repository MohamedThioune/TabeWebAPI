<?php

namespace Database\Seeders;
use App\Domain\Users\ValueObjects\Type;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => Type::Customer]);
        Role::firstOrCreate(['name' => Type::Partner]);
        Role::firstOrCreate(['name' => Type::Enterprise]);
        Role::firstOrCreate(['name' => Type::Admin]);
    }
}

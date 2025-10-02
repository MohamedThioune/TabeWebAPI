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
        Role::create(['name' => Type::Customer]);
        Role::create(['name' => Type::Partner]);
        Role::create(['name' => Type::Enterprise]);
        Role::create(['name' => Type::Admin]);
    }
}

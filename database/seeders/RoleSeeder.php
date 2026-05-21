<?php

namespace Database\Seeders;

use App\Domain\Users\ValueObjects\Type;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => Type::Customer, 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => Type::Partner, 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => Type::Enterprise, 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => Type::Admin, 'guard_name' => 'api']);

        Role::firstOrCreate(['name' => Type::Customer, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => Type::Partner, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => Type::Enterprise, 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => Type::Admin, 'guard_name' => 'web']);
    }
}

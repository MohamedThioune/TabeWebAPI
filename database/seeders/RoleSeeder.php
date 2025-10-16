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
        Role::firstOrCreate(['name' => Type::Customer, 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => Type::Partner, 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => Type::Enterprise, 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => Type::Admin, 'guard_name' => 'api']);

//        Role::firstOrCreate(['name' => Type::Customer, 'guard_name' => 'web']);
//        Role::firstOrCreate(['name' => Type::Partner, 'guard_name' => 'web']);
//        Role::firstOrCreate(['name' => Type::Enterprise, 'guard_name' => 'web']);
//        Role::firstOrCreate(['name' => Type::Admin, 'guard_name' => 'web']);
    }
}

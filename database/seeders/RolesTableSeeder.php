<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => '一般職員'],
            ['name' => '所属長'],
            ['name' => '人事担当者'],
            ['name' => 'システム管理者'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}

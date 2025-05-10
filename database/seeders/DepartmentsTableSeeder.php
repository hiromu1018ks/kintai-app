<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => '総務課'],
            ['name' => '企画調整課'],
            ['name' => '住民福祉課'],
            ['name' => '経済観光課'],
            ['name' => '建設課'],
            ['name' => '教育委員会事務局'],
            // 必要に応じて他の部署も追加
        ];

        foreach ($departments as $department) {
            Department::create($department);
            // もし$fillableを設定していない場合は以下を使用
            // DB::table('departments')->insert($department);
        }
    }
}

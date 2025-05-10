<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkPattern;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Hashファサードをインポート
// Roleモデルをインポート
// Departmentモデルをインポート
// WorkPatternモデルをインポート

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存ユーザーをクリアする場合（開発時）
        User::query()->delete(); // User::truncate() は外部キー制約でエラーになる可能性

        // 管理者ユーザーの作成例
        $adminRole = Role::where('name', 'システム管理者')->first();
        $generalRole = Role::where('name', '一般職員')->first();
        $managerRole = Role::where('name', '所属長')->first();

        $soumuDept = Department::where('name', '総務課')->first();
        $kikakuDept = Department::where('name', '企画調整課')->first();

        $defaultWorkPattern = WorkPattern::where('name', '通常勤務')->first();

        User::create([
            'employee_id' => '00001',
            'name' => '屋久島 太郎 (管理者)',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'department_id' => $soumuDept->id,
            'work_pattern_id' => $defaultWorkPattern->id,
            'job_title' => 'システム管理担当',
            'email_verified_at' => now(),
        ]);

        $supervisor = User::create([
            'employee_id' => '10001',
            'name' => '屋久 徳次郎 (所属長)',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role_id' => $managerRole->id,
            'department_id' => $kikakuDept->id,
            'work_pattern_id' => $defaultWorkPattern->id,
            'job_title' => '課長',
            'email_verified_at' => now(),
        ]);

        User::create([
            'employee_id' => '20001',
            'name' => '屋久島 花子 (一般)',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role_id' => $generalRole->id,
            'department_id' => $kikakuDept->id,
            'work_pattern_id' => $defaultWorkPattern->id,
            'supervisor_id' => $supervisor->id, // 上司を設定
            'job_title' => '主事',
            'email_verified_at' => now(),
        ]);
    }
}

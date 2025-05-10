<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Role;
use App\Models\WorkPattern;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// 追加
// 追加
// 追加 (supervisor_idのため)
// 追加

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // 先にマスタデータを取得しておく（シーダーが実行されている前提）
        $departmentIds = Department::pluck('id')->toArray();
        $roleIds = Role::pluck('id')->toArray();
        $workPatternIds = WorkPattern::pluck('id')->toArray();
        // $userIds = User::pluck('id')->toArray(); // supervisor_idのためだが、鶏と卵問題になるので工夫が必要

        return [
            'employee_id' => $this->faker->unique()->numerify('EMP######'), // 適当な職員番号形式
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // デフォルトパスワード
            'remember_token' => Str::random(10),
            'department_id' => $this->faker->optional()->randomElement($departmentIds), // NULL許容の場合 optional()
            'job_title' => $this->faker->optional()->jobTitle(),
            'role_id' => $this->faker->randomElement($roleIds),
            'work_pattern_id' => $this->faker->optional()->randomElement($workPatternIds),
            'supervisor_id' => null, // supervisor_id は後で設定するか、別の方法で
        ];
    }

    // supervisor_id を設定するための工夫（例）
    // public function configure()
    // {
    //     return $this->afterCreating(function (User $user) {
    //         if (User::count() > 1 && $this->faker->boolean(70)) { // 70%の確率で上司を設定
    //             $user->supervisor_id = User::where('id', '!=', $user->id)->inRandomOrder()->first()->id;
    //             $user->save();
    //         }
    //     });
    // }


    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

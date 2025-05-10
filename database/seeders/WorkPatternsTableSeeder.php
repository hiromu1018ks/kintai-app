<?php

namespace Database\Seeders;

use App\Models\WorkPattern;
use Illuminate\Database\Seeder;

class WorkPatternsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorkPattern::create([
            'name' => '通常勤務',
            'start_time' => '08:30:00',
            'end_time' => '17:15:00',
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);
        // 他の勤務パターンがあれば追加
        // 例：
        // WorkPattern::create([
        //     'name' => '時差出勤A',
        //     'start_time' => '09:00:00',
        //     'end_time' => '17:45:00',
        //     'break_start_time' => '12:00:00',
        //     'break_end_time' => '13:00:00',
        // ]);
    }
}

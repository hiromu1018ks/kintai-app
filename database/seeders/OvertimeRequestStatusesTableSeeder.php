<?php

namespace Database\Seeders;

use App\Models\OvertimeRequestStatus;
use Illuminate\Database\Seeder;

class OvertimeRequestStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => '申請中'],
            ['name' => '承認済'],
            ['name' => '差戻し'],
            ['name' => '取下げ'], // 必要であれば
        ];

        foreach ($statuses as $status) {
            OvertimeRequestStatus::create($status);
        }
    }
}

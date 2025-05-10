<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('勤務形態名称'); // 例: 通常勤務, 早番など
            $table->time('start_time')->comment('始業時刻');
            $table->time('end_time')->comment('終業時刻');
            $table->time('break_start_time')->comment('休憩開始時刻');
            $table->time('break_end_time')->comment('休憩終了時刻');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_patterns');
    }
};

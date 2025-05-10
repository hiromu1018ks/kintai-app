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
        Schema::create('overtime_request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('ステータス名'); // 例: 申請中, 承認済, 差戻し
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_request_statuses');
    }
};
